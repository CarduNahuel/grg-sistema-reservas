<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            
            // Default from
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            throw new \Exception("Email configuration error: " . $e->getMessage());
        }
    }

    public function send($to, $subject, $body, $isHTML = true)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            if ($isHTML) {
                $this->mailer->AltBody = strip_tags($body);
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            return false;
        }
    }

    public function sendReservationCreated($reservation, $user, $restaurant)
    {
        $subject = "Reserva Creada - {$restaurant['name']}";
        
        $body = $this->getTemplate('reservation_created', [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'restaurant_name' => $restaurant['name'],
            'date' => date('d/m/Y', strtotime($reservation['reservation_date'])),
            'time' => date('H:i', strtotime($reservation['start_time'])),
            'guest_count' => $reservation['guest_count']
        ]);

        return $this->send($user['email'], $subject, $body);
    }

    public function sendReservationConfirmed($reservation, $user, $restaurant)
    {
        $subject = "Reserva Confirmada - {$restaurant['name']}";
        
        $body = $this->getTemplate('reservation_confirmed', [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'restaurant_name' => $restaurant['name'],
            'date' => date('d/m/Y', strtotime($reservation['reservation_date'])),
            'time' => date('H:i', strtotime($reservation['start_time'])),
            'guest_count' => $reservation['guest_count'],
            'restaurant_address' => $restaurant['address']
        ]);

        return $this->send($user['email'], $subject, $body);
    }

    public function sendReservationRejected($reservation, $user, $restaurant, $reason = '')
    {
        $subject = "Reserva Rechazada - {$restaurant['name']}";
        
        $body = $this->getTemplate('reservation_rejected', [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'restaurant_name' => $restaurant['name'],
            'date' => date('d/m/Y', strtotime($reservation['reservation_date'])),
            'reason' => $reason
        ]);

        return $this->send($user['email'], $subject, $body);
    }

    public function sendReservationReminder($reservation, $user, $restaurant)
    {
        $subject = "Recordatorio de Reserva - {$restaurant['name']}";
        
        $body = $this->getTemplate('reservation_reminder', [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'restaurant_name' => $restaurant['name'],
            'date' => date('d/m/Y', strtotime($reservation['reservation_date'])),
            'time' => date('H:i', strtotime($reservation['start_time'])),
            'restaurant_address' => $restaurant['address'],
            'restaurant_phone' => $restaurant['phone']
        ]);

        return $this->send($user['email'], $subject, $body);
    }

    private function getTemplate($templateName, $data)
    {
        $templatePath = __DIR__ . '/../../views/emails/' . $templateName . '.php';
        
        if (!file_exists($templatePath)) {
            // Return basic template if file doesn't exist
            return $this->getBasicTemplate($templateName, $data);
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    private function getBasicTemplate($type, $data)
    {
        $html = '<html><body style="font-family: Arial, sans-serif; padding: 20px;">';
        $html .= '<h2 style="color: #333;">GRG - Gestor de Reservas Gastronómicas</h2>';
        
        switch ($type) {
            case 'reservation_created':
                $html .= "<p>Hola {$data['user_name']},</p>";
                $html .= "<p>Tu reserva en <strong>{$data['restaurant_name']}</strong> ha sido creada exitosamente.</p>";
                $html .= "<p><strong>Fecha:</strong> {$data['date']}<br>";
                $html .= "<strong>Hora:</strong> {$data['time']}<br>";
                $html .= "<strong>Personas:</strong> {$data['guest_count']}</p>";
                $html .= "<p>Tu reserva está pendiente de confirmación por parte del restaurante.</p>";
                break;
                
            case 'reservation_confirmed':
                $html .= "<p>Hola {$data['user_name']},</p>";
                $html .= "<p>¡Tu reserva en <strong>{$data['restaurant_name']}</strong> ha sido confirmada!</p>";
                $html .= "<p><strong>Fecha:</strong> {$data['date']}<br>";
                $html .= "<strong>Hora:</strong> {$data['time']}<br>";
                $html .= "<strong>Personas:</strong> {$data['guest_count']}<br>";
                $html .= "<strong>Dirección:</strong> {$data['restaurant_address']}</p>";
                $html .= "<p>Te esperamos!</p>";
                break;
                
            case 'reservation_rejected':
                $html .= "<p>Hola {$data['user_name']},</p>";
                $html .= "<p>Lamentablemente, tu reserva en <strong>{$data['restaurant_name']}</strong> para el {$data['date']} no pudo ser confirmada.</p>";
                if (!empty($data['reason'])) {
                    $html .= "<p><strong>Motivo:</strong> {$data['reason']}</p>";
                }
                break;
                
            case 'reservation_reminder':
                $html .= "<p>Hola {$data['user_name']},</p>";
                $html .= "<p>Te recordamos tu reserva en <strong>{$data['restaurant_name']}</strong>:</p>";
                $html .= "<p><strong>Fecha:</strong> {$data['date']}<br>";
                $html .= "<strong>Hora:</strong> {$data['time']}<br>";
                $html .= "<strong>Dirección:</strong> {$data['restaurant_address']}<br>";
                $html .= "<strong>Teléfono:</strong> {$data['restaurant_phone']}</p>";
                break;
        }
        
        $html .= '<p style="margin-top: 30px; color: #666; font-size: 12px;">Este es un email automático, por favor no respondas.</p>';
        $html .= '</body></html>';
        
        return $html;
    }
}
