<?php

namespace App\Services;

use Ixudra\Curl\Facades\Curl;

class ActiveCampaignService
{
    /** @var string */
    private $url;

    /** @var string */
    private $authHeader;

    /** @var int */
    private $subscribedFieldId;

    public function __construct()
    {
        $this->url = env('ACTIVE_CAMPAIGN_URL');
        $this->authHeader = 'Api-Token: ' . env('ACTIVE_CAMPAIGN_KEY');
        $this->subscribedFieldId = env('ACTIVE_CAMPAIGN_CUSTOMER_ACTIVATED_FIELD_ID');
    }

    public function subscribeUser(string $email)
    {
        $response = Curl::to($this->url . 'contacts')
            ->withHeaders([$this->authHeader])
            ->withData(['email' => $email])
            ->asJsonResponse()
            ->get();

        if ($response->meta->total !== '1') {
            return;
        }

        $contactId = $response->contacts[0]->id;

        $contactFieldValuesLink = $response->contacts[0]->links->fieldValues;

        $contactFieldValues = Curl::to($contactFieldValuesLink)
            ->withHeaders([$this->authHeader])
            ->asJsonResponse()
            ->get();

        $contactFieldValues = $contactFieldValues->fieldValues;

        foreach ($contactFieldValues as $fieldValue) {
            if ($fieldValue->field === $this->subscribedFieldId) {
                $fieldValueId = $fieldValue->id;
            }
        }

        if (!isset($fieldValueId)) {
            $body = [
                'fieldValue' => [
                    'contact' => $contactId,
                    'field' => $this->subscribedFieldId,
                    'value' => 'no',
                ],
            ];

            $response = Curl::to($this->url . 'fieldValues')
                ->withHeaders([$this->authHeader, 'Content-Type: application/json'])
                ->withData(json_encode($body))
                ->asJsonResponse()
                ->post();

            if (isset($response->fieldValue)) {
                $fieldValueId = $response->fieldValue->id;
            } else {
                throw new \Exception("The customer doesn't have a subscription/created a school account field set up!");
            }

        }

        $body = [
            'fieldValue' => [
                'contact' => $contactId,
                'field' => $this->subscribedFieldId,
                'value' => 'yes',
            ],
        ];

        $response = Curl::to($this->url . 'fieldValues/' . $fieldValueId)
            ->withHeaders([$this->authHeader, 'Content-Type: application/json'])
            ->withData(json_encode($body))
            ->asJsonResponse()
            ->put();
    }
}
