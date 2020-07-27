<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryValidationTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateBVIPCamEvents(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPCamEvents');
    }

    public function testValidateBVIPCamImages(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPCamImages');
    }

    public function testValidateBVIPCamReplay(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPCamReplay');
    }

    public function testValidateBVIPConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPConfigurator');
    }
    public function testValidateBVIPDiscovery(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPDiscovery');
    }
    public function testValidateBVIPHealth(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPHealth');
    }
    public function testValidateBVIPInputs(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPInputs');
    }
    public function testValidateBVIPOutputs(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPOutputs');
    }
    public function testValidateBVIPSerialPort(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPSerialPort');
    }
    public function testValidateBVIPSplitter(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPSplitter');
    }
    public function testValidateBVIPVidProc(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPVidProc');
    }
    public function testValidateBVIPVirtualInputs(): void
    {
        $this->validateModule(__DIR__ . '/../BVIPVirtualInputs');
    }
}