<?php

use Cooper\FilamentDcatFilters\Http\Controllers\ModalSelectController;
use Illuminate\Routing\Controller;

describe('ModalSelectController', function () {
    it('can be instantiated', function () {
        $controller = new ModalSelectController;

        expect($controller)->toBeInstanceOf(ModalSelectController::class);
    });

    it('has fetchLabels method', function () {
        expect(method_exists(ModalSelectController::class, 'fetchLabels'))->toBeTrue();
    });

    it('is a controller class', function () {
        $controller = new ModalSelectController;

        expect($controller)->toBeInstanceOf(Controller::class);
    });
});

describe('ModalSelectController Structure', function () {
    it('has correct namespace', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);

        expect($reflection->getNamespaceName())->toBe('Cooper\\FilamentDcatFilters\\Http\\Controllers');
    });

    it('fetchLabels accepts Request parameter', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);
        $method = $reflection->getMethod('fetchLabels');
        $parameters = $method->getParameters();

        expect($parameters)->toHaveCount(1);
        expect($parameters[0]->getName())->toBe('request');
    });

    it('fetchLabels returns JsonResponse', function () {
        $reflection = new ReflectionClass(ModalSelectController::class);
        $method = $reflection->getMethod('fetchLabels');
        $returnType = $method->getReturnType();

        expect($returnType)->not->toBeNull();
        expect($returnType->getName())->toBe('Illuminate\\Http\\JsonResponse');
    });
});
