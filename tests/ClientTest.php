<?php

namespace Ansira\Tests;

use Ansira\Purina\ApiClient;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testBadCacheDirectory()
    {
        try {
            $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint'], [
                'cache_dir' => '/some/local/directory'
            ]);
        } catch (\Exception $e) {
        }

        $this->assertInstanceOf(\Exception::class, $e);

    }
    public function testValidateAccessToken()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $accessToken = $client->getAccessToken();
        $this->assertInternalType('string', $accessToken);
    }

    public function testPostUser()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $email = 'john.connor@ansira.com';
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Connor',
            'email' => $email,
            'subscriptions' => [ 'PE', 'PU' ],
            'sourceCode' => [
                'keyName' => 'ITSP201511'
            ],
            'petOwnershipPlan' => [
                'keyName' => 'DOG'
            ],
            'address' => [
                'postalCode' => '75001'
            ],
        ];
        $user = $client->post('/service/api/v2/users', $userData);
        $this->assertEquals($user->email, $email);
    }

    public function testCreatePet()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $email = 'john.connor@ansira.com';
        $userData = [
            'email' => $email,
            'sourceCode' => [
                'keyName' => 'ITSP201511'
            ],
            'pets' => [
                [
                    'name' => 'Fluffy',
                    'imageUrl' => 'http://www.domain.tld/photos/fluffy.jpg',
                    'petType' => [
                        'keyName' => 'DOG'
                    ],
                    'size' => 'medium',
                    'birthDate' => '2016-01-19',
                    'adoptionDate' => '2016-03-20',
                    'color' => 'brown',
                    'gender' => 'female'
                ]
            ]
        ];
        $user = $client->post('/service/api/v2/users', $userData);
        $petName = null;
        foreach ($user->pets as $pet) {
            if ($pet->name == 'Fluffy') {
                $petName = $pet->name;
                break;
            }
        }
        $this->assertEquals($petName, 'Fluffy');
    }

    public function testInvalidSourceCode()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $email = 'john.connor@ansira.com';
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Connor',
            'email' => $email,
            'subscriptions' => [ 'PE', 'PU' ],
            'sourceCode' => [
                'keyName' => 'INVALID'
            ],
            'petOwnershipPlan' => [
                'keyName' => 'DOG'
            ],
            'address' => [
                'postalCode' => '75001'
            ],
        ];
        try {
            $response = $client->post('/service/api/v2/users', $userData);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(\GuzzleHttp\Exception\ClientException::class, $e);
    }

    public function testInvalidEmail()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $email = 'bademailaddress';
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Connor',
            'email' => $email,
            'subscriptions' => [ 'PE', 'PU' ],
            'sourceCode' => [
                'keyName' => 'INVALID'
            ],
            'petOwnershipPlan' => [
                'keyName' => 'DOG'
            ],
            'address' => [
                'postalCode' => '75001'
            ],
        ];
        try {
            $response = $client->post('/service/api/v2/users', $userData);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(\GuzzleHttp\Exception\ClientException::class, $e);
    }

    public function testFindUserByEmail()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $email = 'john.connor@ansira.com';
        $params = [ 'email' => $email ];
        $users = $client->get('/service/api/v2/users', $params);
        $this->assertCount(1, $users);
    }

    public function testGetBrands()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $brands = $client->get('/service/api/v2/brands');
        $this->assertGreaterThan(0, $brands);
    }

    public function testGetBrandById()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $brand = $client->get('/service/api/v2/brands/1');
        $this->assertEquals($brand->keyName, 'PE');
    }

    public function testGetPetBreeds()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $breeds = $client->get('/service/api/v2/breeds');
        $this->assertGreaterThan(0, $breeds);
    }

    public function testGetPetFoods()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $petFoods = $client->get('/service/api/v2/petfoods');
        $this->assertGreaterThan(0, $petFoods);
    }

    public function testGetPetTypes()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $petTypes = $client->get('/service/api/v2/pettypes');
        $this->assertGreaterThan(0, $petTypes);
    }

    public function testGetPetOwnershipPlans()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $plans = $client->get('/service/api/v2/petownershipplans');
        $this->assertGreaterThan(0, $plans);
    }

    public function testGetCatPetOwnershipPlan()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $catPlan = $client->get('/service/api/v2/petownershipplans/2');
        $this->assertEquals('CAT', $catPlan->keyName);
    }

    public function testGetDogPetType()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $petType = $client->get('/service/api/v2/pettypes/1');
        $this->assertEquals('DOG', $petType->keyName);
    }

    public function testGetCountries()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $countries = $client->get('/service/api/v2/countries');
        $this->assertGreaterThan(0, $countries);
    }

    public function testGetUsa()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $usa = $client->get('/service/api/v2/countries/281');
        $this->assertEquals('US', $usa->keyName);
    }

    public function testGetCurrencies()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $currencies = $client->get('/service/api/v2/currencies');
        $this->assertGreaterThan(0, $currencies);
    }

    public function testGetUsd()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $usd = $client->get('/service/api/v2/currencies/1');
        $this->assertEquals('USD', $usd->keyName);
    }

    public function testGetLanguages()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $languages = $client->get('/service/api/v2/languages');
        $this->assertGreaterThan(0, $languages);
    }

    public function testGetEnglish()
    {
        $client = new ApiClient($_ENV['api_client_id'], $_ENV['api_client_secret'], $_ENV['api_endpoint']);
        $english = $client->get('/service/api/v2/languages/1');
        $this->assertEquals('en', $english->keyName);
    }

}
