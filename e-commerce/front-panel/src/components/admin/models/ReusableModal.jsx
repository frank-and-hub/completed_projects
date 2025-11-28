import React from 'react'
import { Modal, Button } from 'react-bootstrap'

function ReusableModal({ show, handleClose, title, body, primaryAction, secondaryAction, primaryLabel, secondaryLabel, size = 'dialog-centered', secondaryVariant = 'light', primaryVariant = 'custom' }) {
    return (
        <Modal show={show} onHide={handleClose} size={size}>
            <Modal.Header className='border-0' closeButton>
                <Modal.Title className=''>{title}</Modal.Title>
            </Modal.Header>
            <Modal.Body className='text-center border-0 p-0'>{body}</Modal.Body>
            <Modal.Footer className='border-0 center'>
                {secondaryLabel && (
                    <Button className='btn-sm rounded-pill px-3' variant={secondaryVariant} onClick={secondaryAction || handleClose}>
                        {secondaryLabel}
                    </Button>
                )}
                {primaryLabel && (
                    <Button className='btn-sm rounded-pill px-3' variant={primaryVariant} onClick={primaryAction}>
                        {primaryLabel}
                    </Button>
                )}
            </Modal.Footer>
        </Modal>
    );
}

export default ReusableModal;
