<?php

namespace SmartDato\MondialRelayShipping\Data;

use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Data;

class Address extends Data
{
    public function __construct(
        #[Max(40)]
        public string $streetname,
        #[Regex('/^[A-Z]{2}$/')]
        public string $countryCode,
        #[Max(10)]
        public string $postCode,
        #[Max(30)]
        public string $city,
        public ?string $title = null,
        public ?string $firstname = null,
        public ?string $lastname = null,
        #[Max(30)]
        public ?string $addressAdd1 = null,
        #[Max(30)]
        public ?string $addressAdd2 = null,
        #[Max(30)]
        public ?string $addressAdd3 = null,
        #[Max(10)]
        public ?string $houseNo = null,
        #[Max(20)]
        public ?string $phoneNo = null,
        #[Max(20)]
        public ?string $mobileNo = null,
        #[Email, Max(70)]
        public ?string $email = null,
    ) {
        if (! $this->hasName() && ($this->addressAdd1 === null || $this->addressAdd1 === '')) {
            throw InvalidShipmentException::nameOrAddressLineRequired();
        }
    }

    public function hasName(): bool
    {
        return $this->firstname !== null
            && $this->firstname !== ''
            && $this->lastname !== null
            && $this->lastname !== '';
    }

    public function hasPhone(): bool
    {
        return ($this->phoneNo !== null && $this->phoneNo !== '')
            || ($this->mobileNo !== null && $this->mobileNo !== '');
    }
}
