import React from 'react'

function Pageignation({ totalPages, currentPage, handlePageChange }) {
    return (
        <>
            <div className={`pagination justify-content-start`}>
                {Array.from({ length: totalPages }, (_, index) => {
                    const pageNumber = index + 1;
                    if (
                        (pageNumber === 1) ||
                        (pageNumber === totalPages) ||
                        (pageNumber >= currentPage - 1 && pageNumber <= currentPage + 1) ||
                        (pageNumber === 1) ||
                        (pageNumber === totalPages)
                    ) {
                        return (
                            <button
                                key={pageNumber}
                                onClick={() => handlePageChange(pageNumber)}
                                className={`btn btn-light ${currentPage === pageNumber ? `active` : ``}`}
                            >
                                {pageNumber}
                            </button>
                        );
                    }

                    if (
                        (pageNumber === currentPage - 2 && currentPage > 3) ||
                        (pageNumber === currentPage + 2 && currentPage < totalPages - 3)
                    ) {
                        return (
                            <button key={pageNumber} className={`btn btn-light disabled`}>
                                ...
                            </button>
                        );
                    }
                    return null;
                })}
            </div>
        </>
    );
}

export default Pageignation;