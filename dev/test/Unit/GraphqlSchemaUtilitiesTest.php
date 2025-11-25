
<?php

use GPDCore\Library\GraphqlSchemaUtilities;
use PHPUnit\Framework\TestCase;

class GraphqlSchemaUtilitiesTest extends TestCase
{
    public function testExtractQueryBody()
    {
        $query = '
            type Query {
                getUser(id: ID!): User
                listUsers: [User]
            }
        ';
        $expected = 'getUser(id: ID!): User
                listUsers: [User]';
        $result = GraphqlSchemaUtilities::extractQueryBody($query);
        $this->assertEquals($expected, $result);
    }

    public function testExtractQueryBodyEmpty()
    {
        $query = 'type Query {}';
        $expected = '';
        $result = GraphqlSchemaUtilities::extractQueryBody($query);
        $this->assertEquals($expected, $result);
    }

    public function testExtractMutationBody()
    {
        $query = '
            type Mutation {
                createUser(name: String!): User
                deleteUser(id: ID!): Boolean
            }
        ';
        $expected = 'createUser(name: String!): User
                deleteUser(id: ID!): Boolean';
        $result = GraphqlSchemaUtilities::extractMutationBody($query);
        $this->assertEquals($expected, $result);
    }

    public function testExtractMutationBodyEmpty()
    {
        $query = 'type Mutation {}';
        $expected = '';
        $result = GraphqlSchemaUtilities::extractMutationBody($query);
        $this->assertEquals($expected, $result);
    }

    public function testExtractTypes()
    {
        $schema = '
            type Query {
                getUser(id: ID!): User
                listUsers: [User]
            }
            type Mutation {
                createUser(name: String!): User
                deleteUser(id: ID!): Boolean
            }
            type User {
                id: ID!
                name: String!
                email: String!
                accounts: [Account!]
            }
        ';
        $expected = 'type User {
                id: ID!
                name: String!
                email: String!
                accounts: [Account!]
            }';
        $result = GraphqlSchemaUtilities::extractTypes($schema);
        $this->assertEquals($expected, $result);
    }

    public function testCombineSchemas()
    {
        $schema1 = '
        type Query {
            getUser(id: ID!): User
            listUsers: [User]
        }
        type Mutation {
            createUser(name: String!): User
            deleteUser(id: ID!): Boolean
        }
        type User {
            id: ID!
            name: String!
            email: String!
            accounts: [Account!]
        }
    ';
        $schema2 = '
    type Query {
        getAccount(id: ID!): Account
        listAccounts: [Account]
    }
    type Mutation {
        createAccount(name: String!): Account
        deleteAccount(id: ID!): Boolean
    }
    type Account {
        id: ID!
        name: String!
        email: String!
    }
';
        $schemas = [
            $schema1,
            $schema2,
        ];
        $expected = 'type Query {

getUser(id: ID!): User
            listUsers: [User]
getAccount(id: ID!): Account
        listAccounts: [Account]
}
type Mutation {

createUser(name: String!): User
            deleteUser(id: ID!): Boolean
createAccount(name: String!): Account
        deleteAccount(id: ID!): Boolean
}

type User {
            id: ID!
            name: String!
            email: String!
            accounts: [Account!]
        }
type Account {
        id: ID!
        name: String!
        email: String!
    }';
        $result = GraphqlSchemaUtilities::combineSchemas($schemas);
        $this->assertEquals($expected, $result);
    }
}
