<?php
use Carbon_Fields\Carbon_Fields;
use Carbon_Field_Relationship\Relationship_Field;

define( 'Carbon_Field_Relationship\\DIR', __DIR__ );

Carbon_Fields::extend( Relationship_Field::class, function( $container ) {
	return new Relationship_Field( $container['arguments']['type'], $container['arguments']['name'], $container['arguments']['label'] );
} );