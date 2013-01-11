<?php

namespace MVNerds\PropelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MVNerdsPropelBundle extends Bundle
{
	public function getParent()
	{
		return 'PropelBundle';
	}
}
