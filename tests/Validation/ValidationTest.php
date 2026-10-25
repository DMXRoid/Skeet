<?php

declare(strict_types=1);

namespace Skeet\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\ValidationType;
use Skeet\ValidationFactory;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
require_once $lib . 'data_type.class.php';
require_once $lib . 'validation_type.class.php';
require_once $lib . 'factory/validation_factory.class.php';

final class ValidationTest extends TestCase
{
    public function testEmail(): void
    {
        $this->assertTrue(ValidationFactory::validate('a@b.com', ValidationType::Email));
        $this->assertFalse(ValidationFactory::validate('not-an-email', ValidationType::Email));
    }

    public function testExists(): void
    {
        $this->assertTrue(ValidationFactory::validate('x', ValidationType::Exists));
        $this->assertFalse(ValidationFactory::validate('', ValidationType::Exists));
        $this->assertFalse(ValidationFactory::validate(null, ValidationType::Exists));
    }

    public function testInteger(): void
    {
        $this->assertTrue(ValidationFactory::validate(5, ValidationType::Integer));
        $this->assertTrue(ValidationFactory::validate('5', ValidationType::Integer));
        $this->assertFalse(ValidationFactory::validate('5x', ValidationType::Integer));
    }

    public function testBoolean(): void
    {
        $this->assertTrue(ValidationFactory::validate('on', ValidationType::Boolean));
        $this->assertTrue(ValidationFactory::validate(false, ValidationType::Boolean));
        $this->assertFalse(ValidationFactory::validate('maybe', ValidationType::Boolean));
    }

    public function testFromDataTypeMapping(): void
    {
        $this->assertSame(ValidationType::Integer, ValidationType::fromDataType(DataType::TinyInt));
        $this->assertSame(ValidationType::String, ValidationType::fromDataType(DataType::Text));
        $this->assertSame(ValidationType::DateTime, ValidationType::fromDataType(DataType::Time));
    }
}
