<?php
// Namespace : indique que cette classe appartient au module des objets métiers.
namespace App\SAE\Model\DataObject;

// Import de DatabaseConnection (même si ici il n’est pas utilisé directement).
use App\SAE\Model\DatabaseConnection as Model;

// La classe Voiture étend AbstractDataObject et doit donc implémenter formatTableau().
class Point extends AbstractDataObject
{
    private int $id_point;
    private float $latitude;
    private float $longitude;
    private string $geom; // exemple : "POINT(2.35 48.85)"

    public function __construct(int $id_point, float $latitude, float $longitude, string $geom)
    {
        $this->id_point = $id_point;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->geom = $geom;
    }

    /* ============================
       GETTERS
       ============================ */
    public function getIdPoint(): int
    {
        return $this->id_point;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getGeom(): string
    {
        return $this->geom;
    }

    /* ============================
       SETTERS
       ============================ */

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function setGeom(string $geom): void
    {
        $this->geom = $geom;
    }

    /* ============================
       toString()
       ============================ */
    public function __toString(): string
    {
        return "Point #{$this->id_point} — Lat: {$this->latitude}, Lon: {$this->longitude}, Geom: {$this->geom}";
    }

    public function formatTableau(): array {
        return [
            "id_point" => $this->id_point,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "geom" => $this->geom
        ];
    }

}
