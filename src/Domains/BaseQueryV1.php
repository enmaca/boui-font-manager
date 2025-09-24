<?php

namespace Enmaca\Backoffice\FontManager\Domains;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API for managing typography assets, collections, and font management in the backoffice.',
    title: 'Font Manager API'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local development server'
)]
#[OA\Server(
    url: 'https://api.example.com',
    description: 'Production server'
)]
#[OA\Tag(
    name: 'Collections',
    description: 'Font collections and categories management'
)]
#[OA\Tag(
    name: 'Typography',
    description: 'Typography and font file management'
)]
class BaseQueryV1 {}
