<?php

namespace SmartDato\FedEx\Enums;

enum DeletionControlEnum: string
{
    case DELETE_ALL_PACKAGES = 'DELETE_ALL_PACKAGES';
    case DELETE_ONE_PACKAGE = 'DELETE_ONE_PACKAGE';
    case LEGACY = 'LEGACY';
}
