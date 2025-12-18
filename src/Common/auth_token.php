<?php
namespace Src\Common;
define("ACCESS_TOKEN_TTL_SECONDS", 3600); //time out 1 hour
function store_access_token(\mysqli $dbCon, int $userid, string $rawToken, int $ttl = ACCESS_TOKEN_TTL_SECONDS): void
{
    $hash = hash('sha256', $rawToken);
    $expiresAt = date('Y-m-d H:i:s', time() + $ttl); //expires the token within 1 hour after the current time
    $insertPrep = $dbCon->prepare("INSERT INTO api_token (user_id,token_hash,expires_at) VALUES (?,?,?)");
    $insertPrep->bind_param("iss", $userid, $hash, $expiresAt);
    $insertPrep->execute();
}
    function validate_access_token(\mysqli $dbCon, string $rawToken): ?int
{
    $len = strlen($rawToken);
    if ($len < 30 || $len > 200)
        return null;
    $hash = hash('sha256', $rawToken);
    $selectPrep = $dbCon->prepare("SELECT user_id FROM api_token WHERE token_hash=? AND revoked_at is NULL AND expires_at > NOW() LIMIT 1");
    $selectPrep->bind_param("s", $hash);
    $selectPrep->execute();
    $result = $selectPrep->get_result();
    $row = $result->fetch_assoc();
    if (!$row)
        return null;
    $updatePrep = $dbCon->prepare("UPDATE api_token SET last_used_at=NOW() WHERE token_hash=?");
    $updatePrep->bind_param("s", $hash);
    $updatePrep->execute();

    return (int) $row['user_id'];
}

function revoke_access_token(\mysqli $dbCon, string $rawToken): void
{
    $hash = hash('sha256', $rawToken);
    $updatePrep = $dbCon->prepare("UPDATE api_token SET revoked_at=NOW() WHERE token_hash=?");
    $updatePrep->bind_param("s", $hash);
    $updatePrep->execute();
}
function require_auth(\mysqli $dbCon): int
{
    $token = get_bearer_token();
    if ($token === null)
        json_response(json_writer(['error' => 'Missing Bearer Token']), 401);

    $userid = validate_access_token($dbCon, $token);
    if ($userid === null)
        json_response(json_writer(['error' => 'Invalid or expired token.']), 401);

    return $userid;
}
?>