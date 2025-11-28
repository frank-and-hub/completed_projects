import { INestApplication, ValidationPipe } from '@nestjs/common';
import { Test } from '@nestjs/testing';
import * as pactum from 'pactum';
import { AppModule } from '../src/app.module';
import { DbService } from '../src/database';
import { generateRandomParagraph, generateRandomPhoneNumber, generateRandomString } from '../src/common';
import { Gender, DeviceType, RelationshipStatus } from '../src/database';

describe('App e2e', () => {

    let app: INestApplication;
    let database: DbService;

    const FirstName = generateRandomString(10);
    const middleName = generateRandomString(8);
    const LastName = generateRandomString(9);
    const phoneNumber = generateRandomPhoneNumber();
    const email = 'test@yopmail.com';
    const Description = generateRandomParagraph(3);

    const dto = {
        email: email,
        password: 'password@123',
        firstName: FirstName,
        middleName: middleName,
        lastName: LastName,
        phone: phoneNumber,
        isNotify: true,
        isVerified: true,
        gender: Gender.Male,
        deviceType: DeviceType.Other,
        // dateOfBirth: new Date().toISOString().split('T')[0],
        relationshipStatus: RelationshipStatus.ForFun
    };

    beforeAll(async () => {
        const moduleRef = await Test.createTestingModule({
            imports: [AppModule],
        }).compile();
        app = moduleRef.createNestApplication();
        app.useGlobalPipes(new ValidationPipe({ whitelist: true, forbidNonWhitelisted: true, transform: true }));
        await app.init();

        await app.listen(3333);
        pactum.request.setBaseUrl('http://localhost:3333');

        // database = app.get(DbService);
        // await database.cleanDb();
    });

    afterAll(async () => {
        app?.close();
    });

    describe('Auth', () => {
        describe('signup', () => {
            it("should throw an error if email is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-up')
                    .withBody({
                        email: '',
                        password: dto.password,
                        name: dto.firstName
                    })
                    .expectStatus(400)
                    ;
            });

            it("should throw an error if password is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-up')
                    .withBody({
                        email: dto.email,
                        name: dto.firstName
                    })
                    .expectStatus(400)
                    ;
            });

            it("should throw an error if body is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-up')
                    .withBody({})
                    .expectStatus(400)
                    ;
            });

            it("Should signup", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-up')
                    .withBody({
                        email: email,
                        password: dto.password,
                        name: dto.firstName
                    })
                    .expectStatus(201);
            });
        });

        describe('signin', () => {
            it("should throw an error if email is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-in')
                    .withBody({
                        email: '',
                        password: dto.password,
                    })
                    .expectStatus(400);
            });

            it("should throw an error if password is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-in')
                    .withBody({
                        email: dto.email,
                    })
                    .expectStatus(400)
                    ;
            });

            it("should throw an error if body is empty", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-in')
                    .withBody({})
                    .expectStatus(400)
                    ;
            });

            it("Should signin", () => {
                return pactum
                    .spec()
                    .post('/auth/sign-in')
                    .withBody({
                        email: dto.email,
                        password: dto.password
                    })
                    .expectStatus(200)
                    .stores('AccessToken', 'access_token');
            });
        });
    });

    describe('User', () => {
        describe('get profile details', () => {
            it("Should show current user", () => {
                return pactum
                    .spec()
                    .get('/users/profile')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .expectStatus(200)
                    .stores('userId', 'id');
            });
        });

        describe('show all users list', () => {
            it("show all users data in list", () => {
                return pactum
                    .spec()
                    .get('/users')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .withPathParams({ search: null, role: null })
                    .expectStatus(200);
            });
        });

        describe('patch user', () => {
            it("Patch edit user by Id: ", () => {
                return pactum
                    .spec()
                    .patch('/users/$S{userId}')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .withBody({ ...dto })
                    .expectStatus(200);
            });
        });

        describe('put user', () => {
            it("Put edit user by Id: ", () => {
                return pactum
                    .spec()
                    .put('/users/$S{userId}')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .withBody({ ...dto })
                    .expectStatus(200);
            });
        });

        describe("Create a new use", () => {
            it("Add a new user", () => {
                return pactum
                    .spec()
                    .post('/users')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .withBody({
                        email: "newtest@yopmail.com",
                        password: "password@123",
                        firstName: dto.firstName,
                        middleName: dto.middleName,
                        lastName: dto.lastName,
                        isNotify: false,
                        isVerified: true,
                        // gender: Gender.Other,
                        // relationshipStatus: RelationshipStatus.ItsComplicated,
                        // deviceType: DeviceType.Other,
                        // phone: generateRandomPhoneNumber(),
                        // deviceId:generateRandomString(120),

                    })
                    .expectStatus(200);
            })
        });

        describe('delete tested user', () => {
            it("delete a user by Id: ", () => {
                return pactum
                    .spec()
                    .delete('/users/$S{userId}')
                    .withHeaders({
                        Authorization: 'Bearer $S{AccessToken}'
                    })
                    .expectStatus(200);
            });
        });
    });

    describe('Role', () => {
        it("List of all roles", () => {
            return pactum
                .spec()
                .get('/roles')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .expectStatus(200)
        });

        it("create a new role", () => {
            return pactum
                .spec()
                .post('/roles')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withBody({
                    name: 'Admin',
                    description: Description
                })
                .expectStatus(201)
                .stores('roleId', 'id');
        });

        it("get role details", () => {
            return pactum
                .spec()
                .get('/roles/$S{roleId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withPathParams({ search: null })
                .expectStatus(200)
        });

        it("edit role details", () => {
            return pactum
                .spec()
                .patch('/roles/$S{roleId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withBody({
                    name: "Admin",
                    description: "This is a testing Data only",
                    status: true
                })
                .expectStatus(200);
        });

        it("delete role details", () => {
            return pactum
                .spec()
                .delete('/roles/$S{roleId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .expectStatus(200);
        });
    });

    describe('Currency', () => {
        it("List of all currency", () => {
            return pactum
                .spec()
                .get('/currency')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .expectStatus(200)
        });

        it("create a new currency", () => {
            return pactum
                .spec()
                .post('/currency')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withBody({
                    name: 'Admin',
                    shortName: Description,
                    symbol: ';',
                })
                .expectStatus(201)
                .stores('currencyId', 'id');
        });

        it("get currency details", () => {
            return pactum
                .spec()
                .get('/currency/$S{currencyId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withPathParams({ search: null })
                .expectStatus(200)
        });

        it("edit currency details", () => {
            return pactum
                .spec()
                .patch('/currency/$S{currencyId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .withBody({
                    name: "Admin",
                    shortName: "This is a testing Data only",
                    symbol: ';',
                    status: true
                })
                .expectStatus(200);
        });

        it("delete currency details", () => {
            return pactum
                .spec()
                .delete('/currency/$S{currencyId}')
                .withHeaders({
                    Authorization: 'Bearer $S{AccessToken}'
                })
                .expectStatus(200);
        });
    });

    describe('Business', () => {
        it('get business', () => {
            // it.todo("Should show all businesses");
        });
        it('create business', () => {
            // it.todo("Should create a business");
        });
        it('get business by id', () => {
            // it.todo("Should show a business by id");
        });
        it('edit business by id', () => {
            // it.todo("Should edit business details");
        });
        it('delete business by id', () => {
            // it.todo("Should delete a business details");
        });
    });

    // describe('Chat', () => { });

    // it.todo('is the test is running');
});