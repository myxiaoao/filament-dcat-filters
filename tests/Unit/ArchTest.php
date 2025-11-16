<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('globals should not be accessed')
    ->expect('Cooper\FilamentDcatFilters')
    ->not->toUse(['die', 'exit', 'eval']);
