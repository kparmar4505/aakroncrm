<?php

namespace Aakron\Bundle\LeadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AakronLeadBundle extends Bundle
{
    public function getParent()
    {
        return 'OroSalesBundle';
    }
}
