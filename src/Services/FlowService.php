<?php

namespace Katema\WhatsApp\Services;

use Katema\WhatsApp\Models\WhatsAppFlow;
use Katema\WhatsApp\Exceptions\FlowException;

class FlowService
{
    protected \Katema\WhatsApp\Services\Chatbot\FlowCrypto $crypto;

    public function __construct(\Katema\WhatsApp\Services\Chatbot\FlowCrypto $crypto)
    {
        $this->crypto = $crypto;
    }

    public function handleEndpointRequest(array $encryptedData): array
    {
        $privateKey = config('whatsapp.flows.private_key');
        if (!$privateKey) {
            throw new FlowException('Meta Flow Private Key not configured');
        }

        // Decrypt the payload
        $decryptedData = $this->crypto->decrypt(
            $encryptedData['encrypted_flow_data'],
            $privateKey,
            $encryptedData['encrypted_aes_key'],
            $encryptedData['initial_vector']
        );

        // Here the developer would normally process the data...
        // For now, we return a standard response structure
        return [
            'decrypted' => $decryptedData,
            'aes_key' => $encryptedData['encrypted_aes_key'],
            'iv' => $encryptedData['initial_vector']
        ];
    }

    public function encryptResponse(array $responsePayload, string $aesKey, string $iv): string
    {
        return $this->crypto->encrypt($responsePayload, $aesKey, $iv);
    }
    public function createFlow(string $name, array $screens, array $metadata = []): WhatsAppFlow
    {
        $flowId = $this->generateFlowId();

        $definition = [
            'version' => config('whatsapp.flows.version', '7.3'),
            'screens' => $screens,
        ];

        return WhatsAppFlow::create([
            'flow_id' => $flowId,
            'name' => $name,
            'version' => config('whatsapp.flows.version', '7.3'),
            'json_definition' => $definition,
            'metadata' => $metadata,
        ]);
    }

    public function updateFlow(string $flowId, array $screens): WhatsAppFlow
    {
        $flow = WhatsAppFlow::where('flow_id', $flowId)->firstOrFail();

        if ($flow->status === 'published') {
            throw new FlowException('Cannot update published flow. Create a new version instead.');
        }

        $definition = $flow->json_definition;
        $definition['screens'] = $screens;

        $flow->update(['json_definition' => $definition]);

        return $flow;
    }

    public function buildScreen(string $id, string $title, array $layout, ?array $data = null): array
    {
        $screen = [
            'id' => $id,
            'title' => $title,
            'layout' => $layout,
        ];

        if ($data) {
            $screen['data'] = $data;
        }

        return $screen;
    }

    public function buildTextInput(string $name, string $label, bool $required = false, ?string $inputType = null): array
    {
        $component = [
            'type' => 'TextInput',
            'name' => $name,
            'label' => $label,
            'required' => $required,
        ];

        if ($inputType) {
            $component['input-type'] = $inputType;
        }

        return $component;
    }

    public function buildTextArea(string $name, string $label, bool $required = false, ?int $maxLength = null): array
    {
        $component = [
            'type' => 'TextArea',
            'name' => $name,
            'label' => $label,
            'required' => $required,
        ];

        if ($maxLength) {
            $component['max-length'] = $maxLength;
        }

        return $component;
    }

    public function buildCheckboxGroup(string $name, string $label, array $options, bool $required = false): array
    {
        return [
            'type' => 'CheckboxGroup',
            'name' => $name,
            'label' => $label,
            'data-source' => $options,
            'required' => $required,
        ];
    }

    public function buildRadioButtonsGroup(string $name, string $label, array $options, bool $required = false): array
    {
        return [
            'type' => 'RadioButtonsGroup',
            'name' => $name,
            'label' => $label,
            'data-source' => $options,
            'required' => $required,
        ];
    }

    public function buildDropdown(string $name, string $label, array $options, bool $required = false): array
    {
        return [
            'type' => 'Dropdown',
            'name' => $name,
            'label' => $label,
            'data-source' => $options,
            'required' => $required,
        ];
    }

    public function buildDatePicker(string $name, string $label, bool $required = false): array
    {
        return [
            'type' => 'DatePicker',
            'name' => $name,
            'label' => $label,
            'required' => $required,
        ];
    }

    public function buildFooter(string $label, string $onClickAction, ?array $payload = null): array
    {
        $footer = [
            'type' => 'Footer',
            'label' => $label,
            'on-click-action' => $onClickAction,
        ];

        if ($payload) {
            $footer['payload'] = $payload;
        }

        return $footer;
    }

    public function buildForm(array $children): array
    {
        return [
            'type' => 'Form',
            'children' => $children,
        ];
    }

    public function validateFlowJson(array $definition): bool
    {
        if (!isset($definition['version']) || !isset($definition['screens'])) {
            throw new FlowException('Flow must have version and screens');
        }

        if (empty($definition['screens'])) {
            throw new FlowException('Flow must have at least one screen');
        }

        foreach ($definition['screens'] as $screen) {
            if (!isset($screen['id']) || !isset($screen['title']) || !isset($screen['layout'])) {
                throw new FlowException('Each screen must have id, title, and layout');
            }
        }

        return true;
    }

    protected function generateFlowId(): string
    {
        return 'flow_' . uniqid() . '_' . time();
    }
}