import React from 'react'
import ContactForm from './ContactForm'
import 'leaflet/dist/leaflet.css'
import Map from './Map'

function ContactPage() {
    return (
        <>
            <div className={`container-fluid bg-light py-5`}>
                <div className={`col-md-6 m-auto text-center`}>
                    <h1 className={`h1`}>Contact Us</h1>
                    <p>
                        Proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        Lorem ipsum dolor sit amet.
                    </p>
                </div>
            </div>
            <Map />
            <ContactForm />
        </>
    );
}
export default ContactPage;