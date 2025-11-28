const mongoose = require('mongoose');
const axios = require('axios');
const Country = require('../models/country');

// base url
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { status, search } = req.query;

        if (status) filter.status = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Country.countDocuments({
            ...filter
        });

        const query = Country.find(filter)
            .select('_id name dialCode region subregion population cities states currencies currencySymbols flags status slug');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }
        const countrys = await query;

        if (countrys.length === 0) return res.status(200).json({ message: `No countrys found`, data: [] });

        const countryPromises = countrys.map(async (country) => {
            const { _id, name, dialCode, region, subregion, population, cities, states, currencies, currencySymbols, flags, status, slug} = country
            return {
                'id': _id,
                'name': name,
                'dialCode': dialCode,
                'region': region,
                'subregion': subregion,
                'population': population,
                'cities': cities,
                'states': states,
                'currencies': currencies,
                'currencySymbols': currencySymbols,
                'flags': flags,
                'status': status,
                'slug': slug
            }
        });
        const countryResponses = await Promise.all(countryPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: countryResponses
            }, title: 'Country'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    try {

        const apiResponse = await axios.get('https://restcountries.com/v3.1/all');
        const countriesData = apiResponse.data;
        await Country.deleteMany();
        const countryDocuments = countriesData.map((country) => ({
            _id: new mongoose.Types.ObjectId(),
            name: country.name.common,
            dialCode: `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`,
            region: country.region,
            subregion: country.subregion,
            population: country.population,
            flags: country.flags,
            cities: country.capital ? [country.capital[0]] : ['Unknown City'],
            states: country.subregion ? [country.subregion] : ['Unknown State'],
            currencies: country.currencies ? Object.keys(country.currencies) : ['Unknown Currency'],
            currencySymbols: country.currencies ? Object.values(country.currencies).map(cur => cur.symbol) : ['Unknown Symbol'],
        }));
        await Country.insertMany(countryDocuments);
        res.status(201).json({ message: `Your countres lt are created Successfully`, data: [] });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const countryData = await this.findData(id, res);
        const { _id, name, dialCode, region, subregion, population, cities, states, currencies, currencySymbols, flags, status } = countryData;
        const result = {
            'id': _id,
            'name': name,
            'dialCode': dialCode,
            'region': region,
            'subregion': subregion,
            'population': population,
            'cities': cities,
            'states': states,
            'currencies': currencies,
            'currencySymbols': currencySymbols,
            'flags': flags,
            'status': status
        }

        res.status(200).json({ message: `Country data found`, data: result, title: `View ${name} country detail` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Country.findById(identifier): Country.findOne({ slug: identifier });
    
    const countryData = await query
        .select('_id name dialCode region subregion population cities states currencies currencySymbols flags status slug');

    if (!countryData) return res.status(404).json({ message: `Country not found` });
    return countryData;
}