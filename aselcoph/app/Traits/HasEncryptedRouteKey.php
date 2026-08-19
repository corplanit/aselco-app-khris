<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedRouteKey
{
    public function getRouteKey()
    {
        return Crypt::encryptString($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $decryptedId = Crypt::decryptString($value);
            return $this->where($this->getRouteKeyName(), $decryptedId)->first();
        } catch (\Exception $e) {
            $result = $this->where($this->getRouteKeyName(), $value)->first();
            if (!$result) {
                \Log::error('Route binding failed for encrypted ID', [
                    'value' => $value,
                    'model' => get_class($this),
                    'error' => $e->getMessage()
                ]);
            }
            return $result;
        }
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        try {
            $decryptedId = Crypt::decryptString($value);
            return parent::resolveChildRouteBinding($childType, $decryptedId, $field);
        } catch (\Exception $e) {
            return parent::resolveChildRouteBinding($childType, $value, $field);
        }
    }
}
