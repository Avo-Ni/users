<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class LiveChatRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getSalesByDateRange($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT
DATE_FORMAT(FROM_UNIXTIME(creation_date), \'%Y-%m-%d\') as date,
COUNT(*) as sales
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND hotel_ref = :hotelRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAllAssociative($query, $params);

        return $result;
    }

    public function getSalesByDateRangeForGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT
DATE_FORMAT(FROM_UNIXTIME(creation_date), \'%Y-%m-%d\') as date,
COUNT(*) as sales
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
GROUP BY date
ORDER BY date
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAllAssociative($query, $params);

        return $result;
    }

    public function getNumberOfChatsByHotelRef($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as nbChat
FROM T_LIVECHAT_CHATS
WHERE hotel_ref = :hotelRef
AND creation_date BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['nbChat'];
    }

    public function getNumberOfChatsByGroupeRef($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as nbChat
FROM T_LIVECHAT_CHATS
WHERE groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['nbChat'];
    }

    public function caGenererHDB($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT SUM(amount) as ca
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND hotel_ref = :hotelRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['ca'];
    }

    public function caGenererHDBForGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT SUM(amount) as ca
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['ca'];
    }

    public function visitorUnique($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(DISTINCT user_ip) as uni
FROM bookings.T_VISITORS
WHERE hotelRef = :hotelRef
AND FROM_UNIXTIME(start_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['uni'];
    }

    public function visitorUniqueForGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(DISTINCT user_ip) as uni
FROM bookings.T_VISITORS
WHERE groupeRef = :groupeRef
AND FROM_UNIXTIME(start_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['uni'];
    }

    public function panierMoyen($hotelRef, $startDate, $endDate)
    {
        $ca = $this->caGenererHDB($hotelRef, $startDate, $endDate);
        $vente = $this->nbVente($hotelRef, $startDate, $endDate);

        return $ca / $vente;
    }

    public function panierMoyenForGroupe($groupeRef, $startDate, $endDate)
    {
        $ca = $this->caGenererHDBForGroupe($groupeRef, $startDate, $endDate);
        $vente = $this->nbVenteForGroupe($groupeRef, $startDate, $endDate);

        return $ca / $vente;
    }

    public function tauxConversion($hotelRef, $startDate, $endDate)
    {
        $vente = $this->nbVente($hotelRef, $startDate, $endDate);
        $chat = $this->getNumberOfChatsByHotelRef($hotelRef, $startDate, $endDate);
        $r = 0;
        if ($chat > 0) {
            $r = ($vente / $chat) * 100;
        }
        return $r;
    }

    public function tauxConversionForGroupe($groupeRef, $startDate, $endDate)
    {
        $vente = $this->nbVenteForGroupe($groupeRef, $startDate, $endDate);
        $chat = $this->getNumberOfChatsByGroupeRef($groupeRef, $startDate, $endDate);
        $r = 0;
        if ($chat > 0) {
            $r = ($vente / $chat) * 100;
        }
        return $r;
    }

    public function sumComHt($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT SUM(com_amount) as ca_com
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND hotel_ref = :hotelRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['ca_com'];
    }

    public function sumComHtForGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT SUM(com_amount) as ca_com
FROM bookings.T_BOOKINGS_TRANSACTIONS
WHERE is_hdb = 1 AND groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['ca_com'];
    }

    public function coutAcquisition($hotelRef, $startDate, $endDate)
    {
        $cout = $this->sumComHt($hotelRef, $startDate, $endDate) / $this->caGenererHDB($hotelRef, $startDate, $endDate);
        $cout = $cout * 100;
        return $cout;
    }

    public function coutAcquisitionForGroupe($groupeRef, $startDate, $endDate)
    {
        $cout = $this->sumComHtForGroupe($groupeRef, $startDate, $endDate) / $this->caGenererHDBForGroupe($groupeRef, $startDate, $endDate);
        $cout = $cout * 100;
        return $cout;
    }

    public function likesHotel($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as likesh
FROM livechat.T_LIVECHAT_CHATS
WHERE rate = 1 AND hotel_ref = :hotelRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['likesh'];
    }

    public function likesGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as likesh
FROM livechat.T_LIVECHAT_CHATS
WHERE rate = 1 AND groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['likesh'];
    }

    public function nolikesHotel($hotelRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as nolikes
FROM livechat.T_LIVECHAT_CHATS
WHERE rate <> 0 AND hotel_ref = :hotelRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['nolikes'];
    }

    public function nolikesGroupe($groupeRef, $startDate, $endDate)
    {
        $query = '
SELECT COUNT(*) as nolikes
FROM livechat.T_LIVECHAT_CHATS
WHERE rate <> 0 AND groupe_ref = :groupeRef
AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
';

        $params = [
            'groupeRef' => $groupeRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['nolikes'];
    }

    public function nbVenteHotel($hotelRef, $startDate, $endDate)
    {

        $query = '
        SELECT COUNT(*) as nbvente
        FROM livechat.T_LIVECHAT_CHATS 
        WHERE hotel_ref = :hotelRef
        AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate';

        $params = [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ];

        $result = $this->conn->fetchAssociative($query, $params);

        return $result['nbvente'];
    }
    public function getChatsByHotelRef($hotelRef,$startDate, $endDate): array
    {
        $query = '
            SELECT *
            FROM livechat.T_LIVECHAT_CHATS c
            WHERE c.hotel_ref = :hotelRef
            AND FROM_UNIXTIME(creation_date, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
        ';

        return $this->conn->fetchAllAssociative($query, [
            'hotelRef' => $hotelRef,
            'startDate' => $startDate->format('Y-m-d '),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }
    public function getMessagesByChatId($chatId, $startDate, $endDate): array
    {
        $query = '
            SELECT *
            FROM livechat.T_LIVECHAT_MSGS m
            WHERE m.chat_id = :chatId
            AND FROM_UNIXTIME(m.msg_timestamp, \'%Y-%m-%d\') BETWEEN :startDate AND :endDate
        ';

        return $this->conn->fetchAllAssociative($query, [
            'chatId' => $chatId,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ]);
    }



}
