<?php

namespace Tests\Unit;

use App\Services\Base62Encoder;
use Tests\TestCase;

class Base62EncoderTest extends TestCase
{
    public function test_it_applies_a_fixed_offset_before_encoding(): void
    {
        $encoder = new Base62Encoder();

        $this->assertSame('sb', $encoder->encode(1));
        $this->assertSame('sc', $encoder->encode(2));
    }
}
