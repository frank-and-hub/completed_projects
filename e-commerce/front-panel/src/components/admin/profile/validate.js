export default function validate(values) {
    let errors = {}
    if (!values.name) errors.name = 'Please enter user name';
    if (!values.email) errors.email = 'Please enter user email';
    if (!values.phone) errors.phone = 'Please enter phone number';
    if (!values.gender) errors.gender = 'Please select gender number';
    if (!values.about) errors.about = 'Please enter about details';
    if (!values.zipcode) errors.zipcode = 'Please enter zip code';
    if (values.zipcode.length > 6) errors.zipcode = 'Zip code must be 6 digits';
    if (!values.address) errors.address = 'Please enter address details';
    if (!values.city) errors.city = 'Please enter city name';
    if (!values.state) errors.state = 'Please enter state';
    return errors;
}