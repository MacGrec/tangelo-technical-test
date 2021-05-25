# Tangelo Technical Test

# Development process
First at all, I thought to make a recursive algorithm to flat the array and find the depth, but convert
the string to an array without using a parser or library to convert in a "real" array was a problem so,
I decided to treat the string as a tree.

Build the tree using a string had the big problem to treat every possible combination
in the character sequence but after refactor I think, I have a decent solution if we have
in mind all the limitations.

Using the tree, I will could to discover the depth and also the original input, but I decided
save both directly at the database thinking in a time optimization of each request asking
for the list of trees. However, I still keep each tree if we want to use that structure for future features.
Also, when I tried to use the ORM to recover the full structure of the tree I had some problem, the ORM is not able to do it, so this confirmed that, in this first version, it was better use the saved input data at the tree table.

# With more time
- I would like to use a DDD architecture
- To think in a way to reduce the complexity of the code
- To think in a way to build faster the flattened array. The recursive algorithm was faster but maybe harder to maintain.
- Ask the Product Owner about the chance to use any library to facilitate the work ;)
- To think in a way to give more information of why the input is not correct without made the code more complex
- To write more functional test of different bad requests to cover more cases

# How to run
- **up containers:** docker-compose up -d --build
- **Build dataBase:**  docker-compose exec app php bin/console doctrine:migrations:migrate

# Launch tests
- **Unit Tests:** docker-compose exec app bin/phpunit tests/Unit
- **Functional Tests:** docker-compose exec app bin/phpunit tests/Functional

# Postman
Import **tangelo-test.postman_collection.json** using postman to use the api.