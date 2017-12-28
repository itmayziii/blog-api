pipeline {
  agent {
    docker {
      image 'itmayziii/nginx-php70:latest'
      args '-v ${pwd}:/home/forge/default'
    }
    
  }
  stages {
    stage('Install Dependencies') {
      steps {
        sh 'composer install'
      }
    }
    stage('Create .env file') {
      steps {
        sh 'cp .env-local .env'
      }
    }
    stage('DB Migrations') {
      steps {
        sh 'php artisan migrate:refresh --seed --force'
      }
    }
  }
}