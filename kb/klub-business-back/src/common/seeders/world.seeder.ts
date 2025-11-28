import { PrismaClient } from "@prisma/client";
import axios from 'axios';
import { sleep } from "..";

const prisma = new PrismaClient();

const API_BASE = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/';

async function fetchData(endpoint: string) {
    await sleep(2000);
    const response = await axios.get(`${API_BASE}${endpoint}.json`);
    return response.data;
}

export async function createWorldAddress() {
    console.log('Seeding started...');

    await prisma.city.deleteMany();
    await prisma.state.deleteMany();
    await prisma.country.deleteMany();
    // Seed Countries
    const countries = await fetchData('countries');
    const countryData = countries.map(item => ({
        name: item.name,
        iso2: item.iso2,
        iso3: item.iso3,
        code: item?.phonecode ?? item?.phone_code ?? '',
    }));

    await prisma.country.createMany({ data: countryData, skipDuplicates: true });

    // Fetch all countries after insertion
    const countryMap = new Map((await prisma.country.findMany()).map(c => [c.iso2, c.id]));

    // Seed States
    const states = await fetchData('states');
    const stateData = states
        .map((item) => {
            const countryId = countryMap.get(item.country_code);
            return countryId
                ? { name: item.name, countryId }
                : null;
        })
        .filter(Boolean);

    await prisma.state.createMany({ data: stateData, skipDuplicates: true });

    // Fetch all states after insertion
    const stateMap = new Map((await prisma.state.findMany()).map(s => [`${s.name}-${s.countryId}`, s.id]));

    // Seed Cities
    const cities = await fetchData('cities');
    const cityData = cities
        .map((item) => {
            const countryId = countryMap.get(item.country_code);
            const stateId = stateMap.get(`${item.state_name}-${countryId}`);
            return stateId && countryId
                ? { name: item.name, stateId, countryId }
                : null;
        })
        .filter(Boolean);

    await prisma.city.createMany({ data: cityData, skipDuplicates: true });
    console.log('Seeding completed!');
}
