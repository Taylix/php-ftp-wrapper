<?php

namespace Touki\FTP\FTP\Uploader;

use Touki\FTP\FTPWrapper;

/**
 * Non Blocking Resource Uploader
 *
 * @author Touki <g.vincendon@vithemis.com>
 */
class NbResourceUploader extends AbstractNbUploader
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException When $local is not a resource
     */
    public function upload($remoteFile, $local)
    {
        if (!is_resource($local)) {
            throw new \InvalidArgumentException(
                sprintf("Invalid local resource given. Expected resource, got %s", gettype($local))
            );
        }

        $callback = $this->getCallback();
        $this->ftp->pasv(true);

        $state = $this->ftp->fputNb($remoteFile, $local, $this->mode, $this->startPos);
        call_user_func_array($callback, array());

        while ($state == FTPWrapper::MOREDATA) {
            $state = $this->ftp->nbContinue();

            call_user_func_array($callback, array());
        }

        return $state === FTPWrapper::FINISHED;
    }
}
