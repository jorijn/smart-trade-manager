workflow "Lint, test, build & deploy the code" {
  resolves = [
    "Build the production backend",
    "Build the production frontend",
  ]
  on = "push"
}

action "Build the testing backend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/backend/Dockerfile.test -t ci-backend-$GITHUB_SHA:latest ."
}

action "Build the testing frontend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/frontend/Dockerfile.test -t ci-frontend-$GITHUB_SHA:latest ."
}

action "Run PHPUnit" {
  uses = "actions/docker/cli@master"
  needs = ["Build the testing backend"]
  args = "run ci-backend-$GITHUB_SHA:latest /app/bin/phpunit"
}

action "Run ESLint" {
  uses = "actions/docker/cli@master"
  needs = ["Build the testing frontend"]
  args = "run ci-frontend-$GITHUB_SHA:latest cd /app/ && npm run lint"
}

action "Only continue when event is creating a new tag" {
  uses = "actions/bin/filter@25b7b846d5027eac3315b50a8055ea675e2abd89"
  needs = ["Run PHPUnit", "Run ESLint"]
  args = "tag"
}

action "Build the production backend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/backend/Dockerfile -t smarttrademanager/backend:latest ."
  needs = ["Only continue when event is creating a new tag"]
}

action "Build the production frontend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/frontend/Dockerfile -t smarttrademanager/frontend:latest ."
  needs = ["Only continue when event is creating a new tag"]
}
