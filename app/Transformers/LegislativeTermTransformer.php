<?php

namespace App\Transformers;

use OParl\Server\Model\OParl10LegislativeTerm;

class LegislativeTermTransformer extends BaseTransformer
{
    public function transform(OParl10LegislativeTerm $legislativeTerm)
    {
        $data = array_merge($this->getDefaultAttributesForEntity($legislativeTerm), [
            'name'      => $legislativeTerm->name,
            'startDate' => $this->formatDate($legislativeTerm->start_date),
            'endDate'   => $this->formatDate($legislativeTerm->end_date),
            'license'   => $legislativeTerm->license,
        ]);

        if (!$this->isIncluded()) {
            $data['body'] = route('api.oparl.v1.body.show', $legislativeTerm->body_id);
        }

        return $this->cleanupData($data, $legislativeTerm);
    }
}
