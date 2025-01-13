<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class BookingRepository
{
    private $bookingConn;

    public function __construct(Connection $bookingConn)
    {
        $this->bookingConn = $bookingConn;
    }

}
