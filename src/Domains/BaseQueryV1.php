<?php

namespace Enmaca\Backoffice\FontManager\Domains;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API for managing backoffice product designer.',
    title: 'Backoffice Product Designer API'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local server'
)]
class BaseQueryV1 {}
