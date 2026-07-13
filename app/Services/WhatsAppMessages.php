<?php
namespace App\Services;

class WhatsAppMessages
{
    public static function forStatus(string $status, string $patientName, array $extra = []): string
    {
        $name = trim($patientName);
        $bookingId = !empty($extra['booking_id']) ? " (#{$extra['booking_id']})" : '';

        return match ($status) {
            'booking_created' =>
                "*Booking Confirmed{$bookingId}*\n\n" .
                "Hi {$name}, your lab test booking is confirmed.\n\n" .
                "Our phlebotomist will be assigned shortly and you'll receive their details along with an estimated arrival time.\n\n" .
                "Need help? Just reply here.\n\n" .
                "Thank you for choosing Infinity HealthCare — your health, our priority!",

            'Phlebotomist Assigned' =>
                "*Phlebotomist Assigned{$bookingId}*\n\n" .
                "Hi {$name}, your sample collection has been scheduled.\n\n" .
                (!empty($extra['phleb_name']) ? "Phlebotomist: {$extra['phleb_name']}\n" : '') .
                (!empty($extra['eta']) ? "Estimated Arrival: " . date('d M, g:i A', strtotime($extra['eta'])) . "\n" : '') .
                "\nPlease keep the patient available at the given address around this time.\n\n" .
                "*Infinity HealthCare*",

            'Arrived' =>
                "*Phlebotomist Has Arrived{$bookingId}*\n\n" .
                "Hi {$name}, our phlebotomist is at your doorstep and ready for sample collection.\n\n" .
                "Thank you for your cooperation!\n\n" .
                "*Infinity HealthCare*",

            'Sample Collected' =>
                "*Sample Collected{$bookingId}*\n\n" .
                "Hi {$name}, your sample has been collected successfully and is on its way to the lab.\n\n" .
                "We'll notify you as soon as your report is ready.\n\n" .
                "*Infinity HealthCare*",

            'Report Ready' =>
                "*Report Ready{$bookingId}*\n\n" .
                "Hi {$name}, your lab report is now ready.\n\n" .
                (!empty($extra['report_url'])
                    ? "Download here:\n{$extra['report_url']}\n\n"
                    : "Please contact us to download it.\n\n") .
                "*Infinity HealthCare*",

            default =>
                "*Booking Update{$bookingId}*\n\n" .
                "Hi {$name}, your booking status has been updated to: *{$status}*\n\n" .
                "*Infinity HealthCare*",
        };
    }

    public static function forFranchiseAssignment(
        string $franchiseName,
        string $patientName,
        string $phlebName,
        ?string $eta,
        string $address
    ): string {
        $etaText = $eta ? date('d M, g:i A', strtotime($eta)) : 'Not set';

        return "*New Sample Collection Assigned*\n\n" .
            "Patient: {$patientName}\n" .
            "Phlebotomist: {$phlebName}\n" .
            "ETA: {$etaText}\n" .
            "Address: {$address}\n\n" .
            "Login to your dashboard to view full booking details.";
    }

    public static function forTrackingLink(string $patientName, string $trackingUrl): string
    {
        $name = trim($patientName);

        return "*Your Phlebotomist Is On The Way*\n\n" .
            "Hi {$name}, track their live location below:\n" .
            "{$trackingUrl}\n\n" .
            "*Infinity HealthCare*";
    }
}