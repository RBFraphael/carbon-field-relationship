/**
 * External dependencies.
 */
import { registerFieldType } from '@carbon-fields/core';

/**
 * Internal dependencies.
 */
import './style.scss';
import RelationshipField from './main';

registerFieldType( 'relationship', RelationshipField );
