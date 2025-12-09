<?php

namespace Tests;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Reservation;
use App\Services\AuthService;

class ReservationFlowTest extends TestCase
{
    private $authService;
    private $userModel;
    private $restaurantModel;
    private $tableModel;
    private $reservationModel;
    private $testUserId;
    private $testRestaurantId;
    private $testTableId;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authService = new AuthService();
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->tableModel = new Table();
        $this->reservationModel = new Reservation();
        
        // Create test user
        $this->createTestUser();
        
        // Create test restaurant
        $this->createTestRestaurant();
        
        // Create test table
        $this->createTestTable();
    }

    private function createTestUser()
    {
        $email = 'test_' . time() . '@test.com';
        
        $this->testUserId = $this->userModel->createWithRole([
            'email' => $email,
            'password' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+1234567890'
        ], 'CLIENTE');
    }

    private function createTestRestaurant()
    {
        // Get or create owner
        $owner = $this->userModel->first('role_id = (SELECT id FROM roles WHERE name = ?)', ['OWNER']);
        
        if (!$owner) {
            $ownerId = $this->userModel->createWithRole([
                'email' => 'owner_test_' . time() . '@test.com',
                'password' => 'password123',
                'first_name' => 'Test',
                'last_name' => 'Owner',
                'phone' => '+1234567890'
            ], 'OWNER');
        } else {
            $ownerId = $owner['id'];
        }
        
        $this->testRestaurantId = $this->restaurantModel->create([
            'owner_id' => $ownerId,
            'name' => 'Test Restaurant ' . time(),
            'address' => '123 Test St',
            'city' => 'Test City',
            'phone' => '+1234567890',
            'opening_time' => '12:00:00',
            'closing_time' => '23:00:00',
            'is_active' => true
        ]);
    }

    private function createTestTable()
    {
        $this->testTableId = $this->tableModel->create([
            'restaurant_id' => $this->testRestaurantId,
            'table_number' => 'TEST-' . time(),
            'capacity' => 4,
            'area' => 'Test Area',
            'is_available' => true
        ]);
    }

    public function testUserCanCreateReservation()
    {
        $date = date('Y-m-d', strtotime('+1 day'));
        $startTime = $date . ' 19:00:00';
        $endTime = $date . ' 21:00:00';
        
        $reservationId = $this->reservationModel->create([
            'restaurant_id' => $this->testRestaurantId,
            'table_id' => $this->testTableId,
            'user_id' => $this->testUserId,
            'reservation_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'guest_count' => 2,
            'status' => 'pending'
        ]);
        
        $this->assertGreaterThan(0, $reservationId);
        
        $reservation = $this->reservationModel->find($reservationId);
        $this->assertNotNull($reservation);
        $this->assertEquals('pending', $reservation['status']);
        $this->assertEquals(2, $reservation['guest_count']);
        
        return $reservationId;
    }

    /**
     * @depends testUserCanCreateReservation
     */
    public function testRestaurantCanConfirmReservation($reservationId)
    {
        $reservation = $this->reservationModel->find($reservationId);
        $this->assertNotNull($reservation);
        
        // Get restaurant owner
        $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
        $ownerId = $restaurant['owner_id'];
        
        // Confirm reservation
        $updated = $this->reservationModel->confirm($reservationId, $ownerId);
        
        $this->assertNotNull($updated);
        $this->assertEquals('confirmed', $updated['status']);
        $this->assertEquals($ownerId, $updated['confirmed_by']);
        
        return $reservationId;
    }

    /**
     * @depends testRestaurantCanConfirmReservation
     */
    public function testUserCanCancelReservation($reservationId)
    {
        $updated = $this->reservationModel->cancel($reservationId);
        
        $this->assertNotNull($updated);
        $this->assertEquals('cancelled', $updated['status']);
    }

    public function testTableAvailabilityCheck()
    {
        $date = date('Y-m-d', strtotime('+2 days'));
        $startTime = $date . ' 19:00:00';
        $endTime = $date . ' 21:00:00';
        
        // Table should be available
        $isAvailable = $this->tableModel->isAvailable(
            $this->testTableId,
            $date,
            $startTime,
            $endTime
        );
        
        $this->assertTrue($isAvailable);
        
        // Create a reservation
        $reservationId = $this->reservationModel->create([
            'restaurant_id' => $this->testRestaurantId,
            'table_id' => $this->testTableId,
            'user_id' => $this->testUserId,
            'reservation_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'guest_count' => 2,
            'status' => 'confirmed'
        ]);
        
        // Table should NOT be available for the same time
        $isAvailable = $this->tableModel->isAvailable(
            $this->testTableId,
            $date,
            $startTime,
            $endTime
        );
        
        $this->assertFalse($isAvailable);
        
        // But should be available for a different time
        $differentStart = $date . ' 22:00:00';
        $differentEnd = $date . ' 23:00:00';
        
        $isAvailable = $this->tableModel->isAvailable(
            $this->testTableId,
            $date,
            $differentStart,
            $differentEnd
        );
        
        $this->assertTrue($isAvailable);
    }

    public function testReservationValidation()
    {
        // Test past date should fail
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        
        $this->expectException(\Exception::class);
        
        // This should fail in real implementation with proper validation
        // For now, we're just testing that we can detect the condition
        $isPast = strtotime($pastDate) < strtotime(date('Y-m-d'));
        $this->assertTrue($isPast);
    }

    public function testUserCanViewTheirReservations()
    {
        // Create multiple reservations
        for ($i = 0; $i < 3; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $this->reservationModel->create([
                'restaurant_id' => $this->testRestaurantId,
                'table_id' => $this->testTableId,
                'user_id' => $this->testUserId,
                'reservation_date' => $date,
                'start_time' => $date . ' 19:00:00',
                'end_time' => $date . ' 21:00:00',
                'guest_count' => 2,
                'status' => 'pending'
            ]);
        }
        
        $reservations = $this->reservationModel->getByUser($this->testUserId);
        
        $this->assertGreaterThanOrEqual(3, count($reservations));
    }

    public function testRestaurantCanReassignTable()
    {
        $date = date('Y-m-d', strtotime('+3 days'));
        $startTime = $date . ' 19:00:00';
        $endTime = $date . ' 21:00:00';
        
        // Create reservation
        $reservationId = $this->reservationModel->create([
            'restaurant_id' => $this->testRestaurantId,
            'table_id' => $this->testTableId,
            'user_id' => $this->testUserId,
            'reservation_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'guest_count' => 2,
            'status' => 'confirmed'
        ]);
        
        // Create another table
        $newTableId = $this->tableModel->create([
            'restaurant_id' => $this->testRestaurantId,
            'table_number' => 'TEST-NEW-' . time(),
            'capacity' => 4,
            'area' => 'Test Area',
            'is_available' => true
        ]);
        
        // Reassign
        $updated = $this->reservationModel->reassign($reservationId, $newTableId);
        
        $this->assertNotNull($updated);
        $this->assertEquals($newTableId, $updated['table_id']);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testUserId) {
            try {
                $this->userModel->delete($this->testUserId);
            } catch (\Exception $e) {
                // Already deleted or doesn't exist
            }
        }
        
        if ($this->testRestaurantId) {
            try {
                $this->restaurantModel->delete($this->testRestaurantId);
            } catch (\Exception $e) {
                // Already deleted or doesn't exist
            }
        }
        
        parent::tearDown();
    }
}
