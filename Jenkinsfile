pipeline {
  agent any
  stages {
    stage('Install Dependencies') {
      agent {
        docker {
          image 'composer/composer:latest'
        }
        
      }
      steps {
        sh 'composer install --no-interaction'
      }
    }
    stage('Run Unit Tests') {
      agent {
        docker {
          image 'itmayziii/fullheapdeveloper-php:v1'
        }
        
      }
      steps {
        sh 'vendor/bin/phpunit'
      }
    }
    stage('Deploy') {
      agent any
      when {
        branch 'master'
      }
      steps {
        sh 'ssh -i /var/jenkins_home/.ssh/fullheapdeveloper root@165.227.217.233'
        pwd()
      }
    }
  }
}