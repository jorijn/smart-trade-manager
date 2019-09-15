workflow "Lint, test, build & deploy the code" {
  resolves = [
    "Build the production backend",
    "Build the production frontend"
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

action "git.master" {
  uses = "actions/bin/filter@master"
  needs = ["Run PHPUnit", "Run ESLint"]
  args = "branch master"
}

action "Build the production backend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/backend/Dockerfile -t smarttrademanager/backend:latest ."
  needs = ["git.master"]
}

action "Build the production frontend" {
  uses = "actions/docker/cli@master"
  args = "build -f .docker/build/frontend/Dockerfile -t smarttrademanager/frontend:latest ."
  needs = ["git.master"]
}

