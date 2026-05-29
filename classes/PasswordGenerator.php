<?php
// Generates passwords according to configurable character-set parameters
class PasswordGenerator {
    private string $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    private string $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private string $numbers   = '0123456789';
    private string $special   = '!@#$%^&*()_+-=[]{}|;:,.?';

    // Generate a password with exact counts per character type
    // Example: 2 lower + 3 upper + 2 special + 2 numbers = 9-char password
    public function generateByCount(
        int $lowercaseCount,
        int $uppercaseCount,
        int $specialCount,
        int $numbersCount
    ): string {
        $parts = [];

        $parts = array_merge($parts, $this->pickRandom($this->lowercase, $lowercaseCount));
        $parts = array_merge($parts, $this->pickRandom($this->uppercase, $uppercaseCount));
        $parts = array_merge($parts, $this->pickRandom($this->special,   $specialCount));
        $parts = array_merge($parts, $this->pickRandom($this->numbers,   $numbersCount));

        // Shuffle so the character types don't appear in predictable order
        shuffle($parts);
        return implode('', $parts);
    }

    // Generate a password by total length and percentage breakdown
    // Percentages are approximate; remainder goes to lowercase
    public function generateByPercent(
        int   $totalLength,
        float $upperPct,
        float $specialPct,
        float $numbersPct
    ): string {
        $upperCount   = (int) round($totalLength * $upperPct   / 100);
        $specialCount = (int) round($totalLength * $specialPct / 100);
        $numCount     = (int) round($totalLength * $numbersPct / 100);
        $lowerCount   = max(0, $totalLength - $upperCount - $specialCount - $numCount);

        return $this->generateByCount($lowerCount, $upperCount, $specialCount, $numCount);
    }

    // Pick $count random characters from a character set string
    private function pickRandom(string $charset, int $count): array {
        $result = [];
        $len    = strlen($charset);
        for ($i = 0; $i < $count; $i++) {
            $result[] = $charset[random_int(0, $len - 1)];
        }
        return $result;
    }

    // Quick helper: generate with simple total length using all character types
    public function generateSimple(int $length = 16): string {
        $perType = (int) floor($length / 4);
        $extra   = $length - ($perType * 4); // remainder goes to lowercase
        return $this->generateByCount($perType + $extra, $perType, $perType, $perType);
    }
}
