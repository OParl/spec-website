<?php
/**
 * Created by PhpStorm.
 * User: sgraupner
 * Date: 04/01/2017
 * Time: 14:39.
 */

namespace App\Console\Commands;

use App\Services\OParlVersions;
use EFrane\ConsoleAdditions\Batch\Batch;

class InitializeSpecCommand extends SpecCommand
{
    protected $signature = 'oparl:init';

    /**
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $oparlVersions = new OParlVersions();

        $batch = Batch::create($this->getApplication(), $this->getOutput())
            ->add('oparl:update:validator')
            ->add('oparl:update:resources');

        collect($oparlVersions->getModule('specification'))->each(
            function ($specificationConstraint) use ($batch) {
                $batch->add("oparl:update:downloads {$specificationConstraint}");
                $batch->add("oparl:update:specification {$specificationConstraint}");
                $batch->add("oparl:update:schema {$specificationConstraint}");
            }
        );

        return $batch->run();
    }
}
