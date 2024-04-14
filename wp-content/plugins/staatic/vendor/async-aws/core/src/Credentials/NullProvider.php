<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core\Credentials;

use Staatic\Vendor\AsyncAws\Core\Configuration;
final class NullProvider implements CredentialProvider
{
    /**
     * @param \Staatic\Vendor\AsyncAws\Core\Configuration $configuration
     */
    public function getCredentials($configuration)
    {
        return null;
    }
}
