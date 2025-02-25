<?php

namespace SmartDato\FedEx\Contracts;

interface PayloadContract
{
    public function build(): array;
}
