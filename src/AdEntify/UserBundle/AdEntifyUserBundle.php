<?php

namespace AdEntify\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdEntifyUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
