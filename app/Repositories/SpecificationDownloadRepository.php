<?php
/**
 * Created by PhpStorm.
 * User: sgraupner
 * Date: 23/11/2016
 * Time: 11:20.
 */

namespace App\Repositories;

/**
 * Class SpecificationDownloadRepository.
 *
 * Repository for OParl Specification Downloads
 */
class SpecificationDownloadRepository extends DownloadRepository
{
    protected function getIdentifier()
    {
        return 'specification';
    }
}
