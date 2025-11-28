import React, { useState } from 'react'
import config from '../../../../config'

const baseUrl = config.reactApiUrl;

const SearchBar = () => {
    const [query, setQuery] = useState(``);

    const handleSearch = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch(`${baseUrl}/search`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query }),
            });
            await response.json();
        } catch (error) {
            console.error('Error fetching search results:', error);
        }
    }
    return (
        <>
            <div className={`search-bar w-50`}>
                <form encType={`multipart/form-data`} className={`rounded-pill border search-form d-flex align-items-center`} onSubmit={handleSearch}>
                    <input
                        type={`text`}
                        className={`rounded-pill border`}
                        name={`query`}
                        placeholder={`Search`}
                        title={`Enter search keyword`}
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                    />
                    <button type={`submit`} title={`Search`}>
                        <i className={`bi bi-search`}></i>
                    </button>
                </form>
            </div>
        </>
    )
}

export default SearchBar