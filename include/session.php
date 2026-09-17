<?php
declare(strict_types=1);
require_once __DIR__.'/../shared/lifecycle/bootstrap.php';
use CU\IdCard\{Identity,PortalSessionAdapter,Lifecycle,LocalPaymentSimulator};

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode','1');
    if (!session_start(['cookie_httponly'=>true,'cookie_samesite'=>'Lax','cookie_secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'])) throw new RuntimeException('Session storage unavailable.');
}
function currentOfficer(): Identity
{
    $session=$_SESSION ?? [];
    $bypass=getenv('CU_AUTH_BYPASS')==='1' && (PHP_SAPI==='cli' || in_array($_SERVER['REMOTE_ADDR'] ?? '',['127.0.0.1','::1'],true));
    if ($bypass) {
        $session['loginid']='auth-bypass';
        $id=getenv('CU_AUTH_BYPASS_IDCARD_ACTOR') ?: 'DEV_IDCARD_OFFICER';
        $resolver=static fn($login)=>new Identity($id,['id_card_officer']);
    } elseif ($path=getenv('CU_IDCARD_IDENTITY_RESOLVER')) {
        $resolver=require $path;
        if (!$resolver instanceof Closure) throw new RuntimeException('Invalid staff resolver configuration.');
    } else {
        $resolver=static fn($login)=>null;
        if (!isset($session['loginid']) && getenv('CU_IDCARD_DEV_MODE')==='1'
            && (PHP_SAPI==='cli' || in_array($_SERVER['REMOTE_ADDR'] ?? '',['127.0.0.1','::1'],true))
            && ($id=getenv('CU_IDCARD_DEV_ACTOR'))) {
            $session['loginid']='local-development';
            $resolver=static fn($login)=>new Identity($id,['id_card_officer']);
        }
    }
    // CLI has no browser session; its principal must also come from trusted configuration.
    if (PHP_SAPI==='cli' && ($login=getenv('CU_IDCARD_CLI_LOGIN'))) $session['loginid']=$login;
    $actor=(new PortalSessionAdapter($session,$resolver))->current();
    $actor->requireRole('id_card_officer');
    return $actor;
}
function officerCsrfToken(Identity $actor): string
{
    if (($_SESSION['officer_csrf_owner'] ?? null)!==$actor->id || empty($_SESSION['officer_csrf'])) {
        $_SESSION['officer_csrf_owner']=$actor->id;
        $_SESSION['officer_csrf']=bin2hex(random_bytes(32));
    }
    return $_SESSION['officer_csrf'];
}
function requireOfficerCsrf(Identity $actor): void
{
    $token=$_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(officerCsrfToken($actor),$token)) {
        http_response_code(403);
        throw new DomainException('Your session token is invalid. Refresh the page and try again.');
    }
}
function officerLifecycle(Database $db): Lifecycle
{
    return new Lifecycle($db->connection(),new LocalPaymentSimulator(false));
}
