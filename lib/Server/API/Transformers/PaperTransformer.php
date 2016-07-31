<?php

namespace OParl\Server\API\Transformers;

use EFrane\Transfugio\Transformers\BaseTransformer;
use OParl\Server\Model\Paper;

class PaperTransformer extends BaseTransformer
{
    public function transform(Paper $paper)
    {
        return [
            'id'      => route('api.v1.paper.show', $paper),
            'type'    => 'https://schema.oparl.org/1.0/Paper',
            'keyword' => $paper->keywords->pluck('human_name'),
        ];
    }
}