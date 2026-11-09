<?php

declare(strict_types=1);

namespace Youvision\ThinkStoreExample;

use think\response\Json;

class PoemController
{
    public function __construct(protected Poems $poems)
    {
    }

    public function random(): Json
    {
        return json($this->poems->random());
    }
}