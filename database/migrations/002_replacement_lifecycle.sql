-- CU lifecycle v2. MariaDB 10.4+. Run once, with the legacy writers stopped.
-- DDL auto-commits: take a backup first. Additions are restartable on MariaDB.
-- No historical application/payment values or timestamps are rewritten.
SET time_zone = '+01:00';
CREATE TABLE IF NOT EXISTS idcardmigrations (
 version VARCHAR(80) PRIMARY KEY, appliedat DATETIME NOT NULL
) ENGINE=InnoDB;
ALTER TABLE idcardapplications
 ADD COLUMN IF NOT EXISTS paymentstatus ENUM('paid','failed') NULL,
 ADD COLUMN IF NOT EXISTS paidat DATETIME NULL,
 ADD COLUMN IF NOT EXISTS collectedat DATETIME NULL,
 ADD COLUMN IF NOT EXISTS collectedby VARCHAR(100) NULL,
 ADD INDEX IF NOT EXISTS ix_application_student (matricnumber, status),
 ADD INDEX IF NOT EXISTS ix_application_print (paymentstatus, status, paidat);

-- An attempt is separate from an application. Only terminal results create applications.
CREATE TABLE IF NOT EXISTS idcardpaymentattempts (
 attemptid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 paymentreference VARCHAR(100) NOT NULL,
 matricnumber VARCHAR(50) NOT NULL,
 applicationtype ENUM('loststolen','damaged') NOT NULL,
 photopath VARCHAR(255) NOT NULL,
 photosizebytes INT NOT NULL,
 photomimetype VARCHAR(50) NOT NULL,
 paymentoptioncode VARCHAR(50) NOT NULL,
 paymentoptionname VARCHAR(100) NOT NULL,
 baseamount DECIMAL(10,2) NOT NULL,
 chargeamount DECIMAL(10,2) NOT NULL,
 totalamount DECIMAL(10,2) NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'NGN',
 provider VARCHAR(50) NOT NULL DEFAULT 'local-simulator',
 status ENUM('pending','cancelled','paid','failed') NOT NULL DEFAULT 'pending',
 applicationid INT NULL,
 failuremessage VARCHAR(255) NULL,
 createdat DATETIME NOT NULL,
 completedat DATETIME NULL,
 UNIQUE KEY uq_attempt_reference (paymentreference),
 UNIQUE KEY uq_attempt_application (applicationid),
 KEY ix_attempt_student (matricnumber, status),
 CONSTRAINT fk_attempt_application FOREIGN KEY (applicationid) REFERENCES idcardapplications(applicationid)
) ENGINE=InnoDB;
ALTER TABLE paymenttransactions
 ADD COLUMN IF NOT EXISTS attemptid BIGINT UNSIGNED NULL,
 ADD COLUMN IF NOT EXISTS provider VARCHAR(50) NULL,
 ADD COLUMN IF NOT EXISTS currency CHAR(3) NULL,
 ADD COLUMN IF NOT EXISTS completedat DATETIME NULL,
 ADD COLUMN IF NOT EXISTS failuremessage VARCHAR(255) NULL,
 ADD UNIQUE INDEX IF NOT EXISTS uq_transaction_attempt (attemptid);

CREATE TABLE IF NOT EXISTS idcardrefunds (
 refundid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 applicationid INT NOT NULL,
 status ENUM('requested','approved','credited') NOT NULL,
 amount DECIMAL(10,2) NOT NULL,
 requestedat DATETIME NOT NULL,
 requestedby VARCHAR(100) NOT NULL,
 approvedat DATETIME NULL,
 approvedby VARCHAR(100) NULL,
 creditedat DATETIME NULL,
 creditedby VARCHAR(100) NULL,
 UNIQUE KEY uq_refund_application (applicationid),
 KEY ix_refund_queue (status, requestedat),
 CONSTRAINT fk_refund_application FOREIGN KEY (applicationid) REFERENCES idcardapplications(applicationid),
 CONSTRAINT ck_refund_actors CHECK (
   (status='requested' AND approvedat IS NULL AND approvedby IS NULL AND creditedat IS NULL AND creditedby IS NULL)
   OR (status='approved' AND approvedat IS NOT NULL AND approvedby IS NOT NULL AND creditedat IS NULL AND creditedby IS NULL)
   OR (status='credited' AND approvedat IS NOT NULL AND approvedby IS NOT NULL AND creditedat IS NOT NULL AND creditedby IS NOT NULL)
 )
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS idcardapplicationevents (
 eventid BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 applicationid INT NOT NULL,
 eventtype VARCHAR(50) NOT NULL,
 actorid VARCHAR(100) NOT NULL,
 actorrole VARCHAR(50) NOT NULL,
 occurredat DATETIME NOT NULL,
 metadata JSON NOT NULL,
 UNIQUE KEY uq_application_event (applicationid,eventtype),
 KEY ix_history (applicationid,occurredat,eventid),
 CONSTRAINT fk_event_application FOREIGN KEY (applicationid) REFERENCES idcardapplications(applicationid)
) ENGINE=InnoDB;
ALTER TABLE id_card_batch_items
 ADD COLUMN IF NOT EXISTS applicationid INT NULL,
 ADD UNIQUE INDEX IF NOT EXISTS uq_batch_application (batch_id,applicationid),
 ADD INDEX IF NOT EXISTS ix_batch_application (applicationid);
-- Named foreign keys are installed only when absent for interrupted-run recovery.
SET @ddl = IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME='fk_batch_application'), 'SELECT 1', 'ALTER TABLE id_card_batch_items ADD CONSTRAINT fk_batch_application FOREIGN KEY (applicationid) REFERENCES idcardapplications(applicationid)');
PREPARE lifecycle_ddl FROM @ddl; EXECUTE lifecycle_ddl; DEALLOCATE PREPARE lifecycle_ddl;
SET @ddl = IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME='fk_transaction_attempt'), 'SELECT 1', 'ALTER TABLE paymenttransactions ADD CONSTRAINT fk_transaction_attempt FOREIGN KEY (attemptid) REFERENCES idcardpaymentattempts(attemptid)');
PREPARE lifecycle_ddl FROM @ddl; EXECUTE lifecycle_ddl; DEALLOCATE PREPARE lifecycle_ddl;
CREATE TRIGGER IF NOT EXISTS idcardevents_no_update BEFORE UPDATE ON idcardapplicationevents FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Application events are append-only';
CREATE TRIGGER IF NOT EXISTS idcardevents_no_delete BEFORE DELETE ON idcardapplicationevents FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Application events are append-only';
INSERT IGNORE INTO idcardmigrations VALUES ('002_replacement_lifecycle', NOW());
-- Read-only legacy report. NULL paymentstatus means unknown, never failed or paid.
SELECT applicationid, referencenumber, status, printedat,
 CASE WHEN printedat IS NOT NULL AND status NOT IN ('printed','readyforpickup','acknowledged','closed','collected')
 THEN 'Conflicting print evidence: requires reconciliation' ELSE 'Legacy payment/history requires reconciliation' END AS migration_note
 FROM idcardapplications WHERE paymentstatus IS NULL;
