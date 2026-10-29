<?php

namespace Tests\Unit;

use App\Enums\ApplicationStatus;
use PHPUnit\Framework\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_cases_are_in_board_order(): void
    {
        $this->assertSame(
            ['wishlist', 'applied', 'interviewing', 'offer', 'rejected'],
            array_column(ApplicationStatus::cases(), 'value')
        );
    }

    public function test_only_rejected_is_closed_and_labels(): void
    {
        $this->assertTrue(ApplicationStatus::Rejected->isClosed());
        $this->assertFalse(ApplicationStatus::Offer->isClosed());
        $this->assertFalse(ApplicationStatus::Wishlist->isClosed());
        $this->assertSame(['value' => 'interviewing', 'label' => 'Interviewing'], ApplicationStatus::options()[2]);
        $this->assertCount(5, ApplicationStatus::options());
    }
}
