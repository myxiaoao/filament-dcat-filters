<?php

namespace Cooper\FilamentDcatFilters\State;

enum StateType: string
{
    case Single = 'single';
    case Multiple = 'multiple';
    case Range = 'range';
    case Toggle = 'toggle';
    case Keyed = 'keyed';
    case Composite = 'composite';
}
