/**
 * External dependencies.
 */
 import { Component } from '@wordpress/element';

 class RelationshipField extends Component {
	 /**
	  * Handles the change of the input.
	  *
	  * @param  {Object} e
	  * @return {void}
	  */
	 handleChange = ( e ) => {
		 const { id, onChange } = this.props;
 
		 onChange( id, e.target.value );
	 }
 
	 /**
	  * Render a number input field.
	  *
	  * @return {Object}
	  */
	 render() {
		 const {
			 id,
			 name,
			 value,
			 field
		 } = this.props;
		 const { handleChange } = this;
 
		 console.log(field);
 
		 return (
			 <select
				 id={id}
				 name={name}
				 value={value}
				 onChange={this.handleChange}
			 >
				 {
					 field.options.length ?
					 field.options.map((option) => {
						 return (
							 <option value={option.value} selected={value == option.value}>{option.label}</option>
						 )
					 }) :
					 <option value="" disabled={true}>{field.empty}</option>
				 }
			 </select>
		 );
	 }
 }
 
 export default RelationshipField;
 