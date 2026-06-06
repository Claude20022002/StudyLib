<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\DocumentDownload;
use App\Repositories\Contracts\DocumentDownloadRepositoryInterface;

/**
 * @extends BaseRepository<DocumentDownload>
 */
class DocumentDownloadRepository extends BaseRepository implements DocumentDownloadRepositoryInterface
{
    public function __construct(DocumentDownload $model)
    {
        parent::__construct($model);
    }
}
