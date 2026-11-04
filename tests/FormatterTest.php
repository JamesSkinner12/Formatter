<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use JamesSkinner12\Formatter\Formatter;

class FormatterTest extends TestCase
{
    protected Formatter $formatter;

    protected function setUp(): void
    {
        $dataset = json_decode(file_get_contents(__DIR__ . "/sample.json"), true);
        $this->formatter = new Formatter($dataset);
    }

    public function testIsFormatter()
    {
        return $this->assertInstanceOf(Formatter::class, $this->formatter);
    }

    /**
     * Tests that the pipeSchema functionality works as intended and properly keys the array
     */
    public function testPipeSchema()
    {
        $result = $this->formatter->pipeSchema();
        $setToValidate = [
            "1|id",
            "2|id",
            "0|details|eyes",
            "1|details|stench",
            "2|details|eyes"
        ];
        foreach ($setToValidate as $validate) {
            $this->assertArrayHasKey($validate, $result);
        }
    }

    /**
     * Tests that the getItem method behaves as expected.
     * It should get the correct item from the dataset
     */
    public function testCanGetItem()
    {
        $eyes = $this->formatter->getItem("2|details|eyes");
        $this->assertSame("four of them", $eyes);

        // Now verify that the item was actually removed from dataset
        $this->assertArrayHasKey("2|details|eyes", $this->formatter->pipeSchema());
    }

    /**
     * Tests that the pluckItem method behaves as expected.
     * It should get the correct item from the dataset and then also remove it.
     */
    public function testCanPluckItem()
    {
        // Pluck item out of dataset and verify it matches
        $eyes = $this->formatter->pluckItem("2|details|eyes");
        $this->assertSame("four of them", $eyes);

        // Now verify that the item was actually removed from dataset
        $this->assertArrayNotHasKey("2|details|eyes", $this->formatter->pipeSchema());
    }

    /**
     * Test that an item can be properly moved within the dataset.
     * Asserts that the key exists before/after moving and that the value is correct
     */
    public function testCanMoveItem()
    {
        // Verify that the item exists before we try to move it
        $this->assertArrayHasKey("2|details|eyes", $this->formatter->pipeSchema());

        // Now move item to a different spot
        $this->formatter->moveItem("2|details|eyes", "2|eyeColor");
        // Assert that the key exists
        $this->assertArrayHasKey("2|eyeColor", $this->formatter->pipeSchema());
        $this->assertSame("four of them", $this->formatter->getItem("2|eyeColor"));
    }

    /**
     * Tests that an item can be put into the dataset properly.
     */
    public function testCanPutItem()
    {
        // Add a shirtSize to the detail properties of the 3rd entry
        $this->formatter->putItem("2|details|shirtSize", "medium");
        // Assert that the key exists as expected
        $this->assertArrayHasKey("2|details|shirtSize", $this->formatter->pipeSchema());
        // Assert that the value of that array key is correct
        $this->assertSame("medium", $this->formatter->getItem("2|details|shirtSize"));
    }
}
