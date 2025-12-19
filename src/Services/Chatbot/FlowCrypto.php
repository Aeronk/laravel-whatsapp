<?php

namespace Katema\WhatsApp\Services\Chatbot;

use Katema\WhatsApp\Exceptions\WhatsAppException;

class FlowCrypto
{
    /**
     * Decrypt the encrypted payload from Meta Flow request.
     *
     * @param string $encryptedPayload The base64 encoded encrypted payload
     * @param string $privateKey The RSA private key to decrypt the AES key
     * @param string $encryptedAesKey The base64 encoded encrypted AES key
     * @param string $initialVector The base64 encoded IV
     * @return array
     */
    public function decrypt(string $encryptedPayload, string $privateKey, string $encryptedAesKey, string $initialVector): array
    {
        // 1. Decrypt the AES key using the RSA private key
        $aesKey = '';
        if (!openssl_private_decrypt(base64_decode($encryptedAesKey), $aesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new WhatsAppException('Failed to decrypt AES key');
        }

        // 2. Decrypt the payload using the AES key and IV
        $decodedPayload = base64_decode($encryptedPayload);
        $iv = base64_decode($initialVector);

        // Tag is the last 16 bytes of the payload in GCM mode
        $tag = substr($decodedPayload, -16);
        $ciphertext = substr($decodedPayload, 0, -16);

        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new WhatsAppException('Failed to decrypt payload');
        }

        return json_decode($decrypted, true);
    }

    /**
     * Encrypt the response payload for Meta Flow.
     *
     * @param array $payload The response payload to encrypt
     * @param string $aesKey The raw AES key (decrypted from previous step)
     * @param string $initialVector The raw IV (decrypted from previous step)
     * @return string
     */
    public function encrypt(array $payload, string $aesKey, string $initialVector): string
    {
        $jsonPayload = json_encode($payload);
        $iv = base64_decode($initialVector);
        $tag = '';

        $encrypted = openssl_encrypt(
            $jsonPayload,
            'aes-256-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($encrypted === false) {
            throw new WhatsAppException('Failed to encrypt payload');
        }

        return base64_encode($encrypted . $tag);
    }
}
