<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Commands\File;

use Enmaca\Backoffice\FontManager\Exceptions\FontManagerException;
use Exception;
use Uxmal\Backend\Attributes\RegisterCommand;
use Uxmal\Backend\Command\CommandBase;

#[RegisterCommand('/v1/font-manager/typography/file/revert', 'delete', 'cmd.font-manager.typography.file.revert.v1')]
class Revert extends CommandBase
{
    public array $payloadValidator = [
    ];

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): array
    {

        dump($this->payload['content']);

        if (empty($this->payload['content'])) {
            throw new FontManagerException('Typography ID is required', 400);
        }

        /*
        try {
            return $this->createOnModel(DigitalProduct::class, $data);
        } catch (Exception $e) {
            throw new FontManagerException($e->getMessage(), (int) $e->getCode());
        }
        */
        return [];
    }
}
