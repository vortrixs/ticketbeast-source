<?php

namespace Tests\Unit;

use App\HashidsTicketCodeGenerator;
use App\Ticket;
use Tests\TestCase;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function ticket_codes_are_at_least_6_characters_long()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) <= 6);
    }

    /**
     * @test
     */
    public function ticket_codes_can_only_contain_uppcase_letters()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue($code === strtoupper($code));
    }

    /**
 * @test
 */
    public function ticket_codes_for_the_same_ticket_id_are_the_same()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $generator->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /**
     * @test
     */
    public function ticket_codes_for_different_ticket_id_are_different()
    {
        $generator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $generator->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    /**
     * @test
     */
    public function ticket_codes_generated_with_different_salts_are_different()
    {
        $generator1 = new HashidsTicketCodeGenerator('testsalt1');
        $generator2 = new HashidsTicketCodeGenerator('testsalt2');

        $code1 = $generator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $generator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}
