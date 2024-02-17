<?php

namespace App\Utils\Constants\Statuses;

class BORROWED_ITEM_STATUS
{
    const PENDING_APPROVAL = 'PENDING_APPROVAL';
    const IN_POSSESSION = 'IN_POSSESSION';
    const OVERDUE = 'OVERDUE_RETURN';
    const CANCELLED = 'CANCELLED';
    const DISAPPROVED = 'DISAPPROVED';
    const APPROVED = 'APPROVED';
    const UNRELEASED = 'UNRELEASED';
    const UNRETURNED = 'UNRETURNED';
    const RETURNED = 'RETURNED';
    const DAMAGED_BUT_REPAIRABLE = 'DAMAGED_BUT_REPAIRABLE';
    const UNREPAIRABLE = 'UNREPAIRABLE';
    const LOST = 'LOST';
}