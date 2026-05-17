def formatDuration(long ms) {
    def secs = (ms / 1000).toInteger()
    "${(secs / 60).toInteger()}m ${secs % 60}s"
}

pipeline {
    agent any

    // Definición de variables de entorno.
    environment {
        // ── Servidores (credenciales existentes) ──
        SFTP_SERVER           = credentials('SERVER_PRODUCTION')
        SFTP_SERVER_2         = credentials('SERVER_STAGING')
        SFTP_PATH_PRODUCTION  = credentials('PATH_PRODUCTION')
        SFTP_PATH_DEVELOPMENT = credentials('PATH_DEVELOPMENT')
        SFTP_PATH_STAGING     = credentials('SFTP_PATH_STAGING')
        SFTP_USERNAME         = credentials('SSH_USERNAME')
        DISCORD_DEPLOY_WEBHOOK = credentials('DISCORD_DEPLOY_WEBHOOK')

        // ── Docker Registry (GHCR) ──
        GHCR_CREDS  = credentials('github-global-token')   // usernamePassword: GitHub user + PAT (write:packages)
        REGISTRY    = 'ghcr.io'
        IMAGE_REPO  = 'daruinherreraigniweb/amazingsite-erp'
    }

    stages {
        stage('Checkout') {
            steps {
                script {
                    try {
                        checkout scm
                        env.GIT_COMMIT_HASH   = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
                        env.GIT_COMMIT_MSG    = sh(script: 'git log -1 --pretty=%s',      returnStdout: true).trim()
                        env.GIT_COMMIT_AUTHOR = sh(script: 'git log -1 --pretty=%an',     returnStdout: true).trim()
                        env.BUILD_DATE        = new Date().format('dd/MM/yyyy HH:mm:ss z', TimeZone.getTimeZone('America/Lima'))
                        env.FAILED_STAGE      = ''
                    } catch (e) {
                        env.FAILED_STAGE = 'Checkout'
                        throw e
                    }
                }
            }
        }

        // Validar la integridad del PR: solo develop y main, haciendo uso de docker para garantizar la misma versión de PHP que en desarrollo, y aplicando validaciones adicionales como Conventional Commits, detección de código de debug, y validación del composer.json.
        stage('Lint & Standards') {
            when { 
                not { 
                    anyOf { 
                        branch 'main'
                        branch 'staging' 
                    } 
                } 
            }

            // Docker guarantees the same PHP version used in development, avoiding "php: not found" on the Jenkins agent.
            agent { docker { image 'php:8.3-cli-alpine'; reuseNode true } }
            steps {
                script {
                    try {
                        // Install Git + Composer 2 into the PHP 8.3 Alpine container, which is required for the validations and also ensures that the composer.lock file is up to date with composer.json before deployment.
                        sh '''
                            apk add --no-cache git > /dev/null
                            curl -sS https://getcomposer.org/composer-2.phar -o /usr/local/bin/composer
                            chmod +x /usr/local/bin/composer
                        '''

                        // 1. Formato validación del mensaje de commit (Conventional Commits)
                        def commitMsg = sh(script: 'git log -1 --pretty=%B', returnStdout: true).trim()
                        def pattern = /^(feat|fix|docs|style|refactor|perf|test|chore|infra)(\(.+\))?: .{1,100}/
                        if (!(commitMsg =~ pattern)) {
                            def guide = [
                                '❌ COMMIT RECHAZADO — El mensaje no sigue Conventional Commits.',
                                "   Recibido : \"${commitMsg.take(80)}\"",
                                '   Formato  : tipo(scope): descripción   (scope es opcional)',
                                '   Tipos    : feat|fix|docs|style|refactor|perf|test|chore|infra',
                                '   Ejemplo  : feat(auth): agregar login con Google',
                            ].join('\n')
                            error guide
                        }

                        // 2. Obtener archivos PHP modificados en el PR (o commit si no es PR), para calidadr solo los archivos relevantes y evitar falsos positivos en código no modificado.
                        def diffBase = env.CHANGE_TARGET ? "origin/${env.CHANGE_TARGET}" : 'HEAD~1'
                        def changedFiles = sh(
                            script: "git diff --name-only --diff-filter=d ${diffBase}...HEAD -- '*.php' || true",
                            returnStdout: true
                        ).trim()

                        // 3. Sintaxis PHP en todos los archivos modificados
                        if (changedFiles) {
                            sh """
                                echo '${changedFiles}' | while IFS= read -r file; do
                                    if [ -f "\$file" ]; then php -l "\$file"; fi
                                done
                            """
                        }

                        // 4. Detectar código de debug accidental (solo en archivos modificados).
                        if (changedFiles) {
                            def debugFound = sh(
                                script: """
                                    echo '${changedFiles}' | while IFS= read -r file; do
                                        [ -f "\$file" ] && echo "\$file"
                                    done | xargs -r grep -n -E '\\bdd\\(|\\bdump\\(|\\bvar_dump\\(|\\bprint_r\\(' || true
                                """,
                                returnStdout: true
                            ).trim()
                            if (debugFound) {
                                error "❌ Se encontraron funciones de debug en el código:\n${debugFound}"
                            }
                        }

                        // 5. Validar integridad del composer.json para evitr errores
                        sh 'composer validate --no-check-all --no-interaction --quiet'

                    } catch (e) {
                        env.FAILED_STAGE = 'Lint & Standards'
                        throw e
                    }
                }
            }
        }

        stage('SonarQube Analysis') {
            // Corre en cualquier PR (CHANGE_ID presente) o commit hacia develop.
            // Esto garantiza que la calidad se evalúa antes de integrar el código, no solo después.
            // Se excluye main: ya fue validado cuando era PR hacia develop.
            when { anyOf { branch 'develop'; expression { env.CHANGE_ID != null } } }
            steps {
                script {
                    try {
                        // 'SonarScanner': debe coincidir exactamente con el nombre configurado en Jenkins (Manage Jenkins > Global Tool Configuration)
                        def scannerHome = tool 'SonarScanner' 
                        
                        withSonarQubeEnv('SonarQube') { 
                            sh """
                                export SONAR_SCANNER_OPTS="-Xmx512m -Xms256m"
                                ${scannerHome}/bin/sonar-scanner \
                                -Dsonar.projectKey=Amazingsite-erp \
                                -Dsonar.sources=app,Modules,database,routes \
                                -Dsonar.inclusions=**/*.php \
                                -Dsonar.host.url=https://sonar.igni-soft.com/ \
                                -Dsonar.exclusions=**/vendor/**,**/node_modules/**,storage/**,public/**,docs/**,resources/views/**,database/migrations/**,database/seeders/**,database/factories/**,Modules/**/Database/Migrations/**,Modules/**/Database/Seeders/**,Modules/**/Database/factories/**,Modules/**/Resources/views/**,Modules/**/Resources/lang/**,Modules/**/Tests/**,tests/** \
                                -Dsonar.php.exclusions=**/vendor/** \
                                -Dsonar.php.coverage.reportPaths= \
                                -Dsonar.javascript.skip=true \
                                -Dsonar.language=php \
                                -Dsonar.cpd.exclusions=** \
                                -Dsonar.working.directory=.scannerwork
                            """
                        }
                        // waitForQualityGate must be called OUTSIDE withSonarQubeEnv.
                        // abortPipeline: false → el deploy continúa; el estado real llega a Discord.
                        // Cambia a abortPipeline: true si quieres bloquear el despliegue cuando QG falla.
                        def qg = waitForQualityGate(abortPipeline: false)
                        env.SONAR_QG_STATUS = qg.status   // 'OK' | 'ERROR' | 'WARN' | 'NONE'
                    } catch (e) {
                        env.FAILED_STAGE = 'SonarQube Analysis'
                        throw e
                    }
                }
            }
        }

        stage('Determine Environment') {
            when { anyOf { branch 'main'; branch 'develop'; branch 'staging' } }
            steps {
                script {
                    try {
                        if (env.BRANCH_NAME == 'main') {
                            env.DEPLOY_SERVER       = env.SFTP_SERVER
                            env.DEPLOY_PATH         = env.SFTP_PATH_PRODUCTION
                            env.DEPLOY_ENV          = 'PRODUCCIÓN'
                            env.IMAGE_TAG           = 'latest'
                            env.COMPOSE_PROJECT     = 'production'
                            env.APP_PORT            = '8081'
                        } else if (env.BRANCH_NAME == 'staging') {
                            env.DEPLOY_SERVER       = env.SFTP_SERVER_2
                            env.DEPLOY_PATH         = env.SFTP_PATH_STAGING
                            env.DEPLOY_ENV          = 'STAGING'
                            env.IMAGE_TAG           = 'staging-latest'
                            env.COMPOSE_PROJECT     = 'staging'
                            env.APP_PORT            = '8080'
                        } else {
                            env.DEPLOY_SERVER       = env.SFTP_SERVER_2
                            env.DEPLOY_PATH         = env.SFTP_PATH_DEVELOPMENT
                            env.DEPLOY_ENV          = 'DEVELOPMENT'
                            env.IMAGE_TAG           = 'develop-latest'
                            env.COMPOSE_PROJECT     = 'develop'
                            env.APP_PORT            = '8082'
                        }
                        // Nombres completos de imagen para build/push/deploy
                        env.APP_IMAGE       = "${REGISTRY}/${IMAGE_REPO}:${env.IMAGE_TAG}"
                        env.NGINX_IMAGE     = "${REGISTRY}/${IMAGE_REPO}:nginx-${env.IMAGE_TAG}"
                        env.APP_IMAGE_SHA   = "${REGISTRY}/${IMAGE_REPO}:${env.BRANCH_NAME}-${env.GIT_COMMIT_HASH}"
                        env.NGINX_IMAGE_SHA = "${REGISTRY}/${IMAGE_REPO}:nginx-${env.BRANCH_NAME}-${env.GIT_COMMIT_HASH}"
                    } catch (e) {
                        env.FAILED_STAGE = 'Determine Environment'
                        throw e
                    }
                }
            }
        }

        // Construir las imágenes Docker (app + nginx) y subir a GHCR.
        // Se generan dos tags por imagen: rolling (:latest/:develop-latest) y fijo (:branch-SHA).
        // El tag fijo permite rollback instantáneo sin reconstruir.
        // --cache-from reutiliza capas de la imagen anterior → solo reconstruye lo que cambió.
        // Primera ejecución: ~7min (sin caché). Siguientes: ~1-2min (solo COPY código).
        stage('Build & Push') {
            when { anyOf { branch 'main'; branch 'staging' } }
            steps {
                script {
                    try {
                        // Login a GitHub Container Registry
                        sh '''
                            set +x
                            echo "${GHCR_CREDS_PSW}" | docker login ${REGISTRY} -u "${GHCR_CREDS_USR}" --password-stdin
                            set -x
                        '''

                        // La imagen anterior en GHCR tiene un snapshot de BuildKit corrompido.
                        // Eliminar --cache-from y BUILDKIT_INLINE_CACHE evita que BuildKit
                        // intente restaurar esas capas corruptas del registry.
                        // El daemon usa su caché local entre builds del mismo nodo.
                        sh "docker builder prune -af"

                        // Construir imagen PHP-FPM (app, worker, scheduler)
                        sh """
                            docker build \
                                -t ${env.APP_IMAGE} \
                                -t ${env.APP_IMAGE_SHA} \
                                --target production \
                                -f Dockerfile .
                        """

                        // Construir imagen Nginx (estáticos + config)
                        sh """
                            docker build \
                                -t ${env.NGINX_IMAGE} \
                                -t ${env.NGINX_IMAGE_SHA} \
                                -f docker/nginx/Dockerfile .
                        """

                        // Push rolling tag + SHA tag en paralelo
                        sh """
                            docker push ${env.APP_IMAGE} &
                            docker push ${env.NGINX_IMAGE} &
                            wait
                            docker push ${env.APP_IMAGE_SHA} &
                            docker push ${env.NGINX_IMAGE_SHA} &
                            wait
                        """

                        // Logout del registry
                        sh "docker logout ${REGISTRY}"
                    } catch (e) {
                        env.FAILED_STAGE = 'Build & Push'
                        throw e
                    }
                }
            }
        }

        // Desplegar al servidor: sincronizar compose, pull imágenes, levantar contenedores.
        // El entrypoint.sh del contenedor app ejecuta automáticamente:
        //   - migrate + module:migrate
        //   - seed:deploy (main) o seed:staging (staging)
        //   - config/route/view/event cache
        stage('Deploy') {
            when { anyOf { branch 'main'; branch 'staging' } }
            steps {
                script {
                    try {
                        sshagent(credentials: ['SSH_KEY']) {
                            // 1. Sincronizar docker-compose y .env.docker al servidor
                            sh """
                                scp -o StrictHostKeyChecking=no -P 22 \
                                    docker-compose.yml .env.docker \
                                    ${SFTP_USERNAME}@${env.DEPLOY_SERVER}:${env.DEPLOY_PATH}/
                            """

                            // 2. Pull + restart en el servidor
                            sh """
                                ssh -o StrictHostKeyChecking=no -p 22 ${SFTP_USERNAME}@${env.DEPLOY_SERVER} '
                                    set -e
                                    cd ${env.DEPLOY_PATH}

                                    # Inyectar tags de imagen correctos en .env del servidor
                                    sed -i "/APP_IMAGE=/d; /NGINX_IMAGE=/d; /COMPOSE_PROJECT_NAME=/d; /APP_PORT=/d" .env
                                    printf "\nAPP_IMAGE=${env.APP_IMAGE}\nNGINX_IMAGE=${env.NGINX_IMAGE}\nCOMPOSE_PROJECT_NAME=${env.COMPOSE_PROJECT}\nAPP_PORT=${env.APP_PORT}\n" >> .env

                                    # Permisos del .env: www-data (uid 33) necesita poder escribirlo
                                    chmod 666 .env

                                    # Autenticar en GHCR para pull
                                    set +x
                                    echo "${GHCR_CREDS_PSW}" | docker login ${REGISTRY} -u "${GHCR_CREDS_USR}" --password-stdin
                                    set -x

                                    # Descargar nuevas imágenes
                                    docker compose pull

                                    # Bajar el stack actual completamente antes de levantar el nuevo.
                                    # Más robusto que filtrar por puerto: elimina contenedores de
                                    # cualquier nombre de proyecto anterior, redes y conflictos de estado
                                    # (Restarting, Exited) sin depender de heurísticas de filtrado.
                                    docker compose down --remove-orphans || true

                                    # Levantar el stack con las nuevas imágenes
                                    docker compose up -d

                                    # Esperar a que el contenedor app esté listo (máx 120s)
                                    echo "[deploy] Esperando que app esté listo..."
                                    for i in \$(seq 1 120); do
                                        if docker compose exec -T app php artisan --version > /dev/null 2>&1; then
                                            echo "[deploy] App lista después de \${i}s"
                                            break
                                        fi
                                        if [ "\$i" -eq 120 ]; then
                                            echo "[deploy] TIMEOUT: app no respondió en 120s"
                                            docker compose logs --tail=50 app
                                            exit 1
                                        fi
                                        sleep 1
                                    done

                                    # Verificar que todos los servicios estén corriendo
                                    docker compose ps --format "table {{.Name}}\t{{.Status}}"

                                    # Limpiar imágenes antiguas, build cache y containers parados
                                    docker system prune -af --filter "until=72h"
                                    docker builder prune -af --filter "until=72h"

                                    docker logout ${REGISTRY}
                                '
                            """
                        }
                    } catch (e) {
                        env.FAILED_STAGE = 'Deploy'
                        throw e
                    }
                }
            }
        }
    }

    post {
        success {
            script {
                def sonarLink  = 'https://sonar.igni-soft.com/dashboard?id=Amazingsite-erp'
                def qgStatus   = env.SONAR_QG_STATUS  // 'OK' | 'ERROR' | 'WARN' | 'NONE' | null
                def qgEmoji    = qgStatus == 'OK'    ? '✅' :
                                 qgStatus == 'WARN'  ? '⚠️' :
                                 qgStatus == 'ERROR' ? '❌' :
                                 qgStatus == 'NONE'  ? 'ℹ️' : null
                def sonarValue = qgEmoji
                    ? "${qgEmoji} **${qgStatus}** — [Ver Reporte](${sonarLink})"
                    : "No aplica (rama ${env.BRANCH_NAME})"

                def payload = groovy.json.JsonOutput.toJson([
                    embeds: [[
                        title: '✅ Despliegue Exitoso',
                        description: "**${env.JOB_NAME}** se ha actualizado correctamente.",
                        color: 3066993,
                        fields: [
                            [name: '📝 Commit',    value: "`${env.GIT_COMMIT_HASH}` — ${env.GIT_COMMIT_MSG}", inline: false],
                            [name: '👤 Autor',     value: env.GIT_COMMIT_AUTHOR ?: 'N/A',                    inline: true],
                            [name: '🌍 Entorno',   value: env.DEPLOY_ENV ?: 'N/A',                          inline: true],
                            [name: '🌿 Rama',      value: "`${env.BRANCH_NAME}`",                           inline: true],
                            [name: '🔢 Build',     value: "[#${env.BUILD_NUMBER}](${env.BUILD_URL})",        inline: true],
                            [name: '⏱️ Duración',  value: formatDuration(currentBuild.duration),             inline: true],
                            [name: '🕐 Fecha',     value: env.BUILD_DATE ?: 'N/A',                          inline: true],
                            [name: '📊 SonarQube', value: sonarValue,                                        inline: false]
                        ],
                        footer: [text: 'Jenkins CI/CD']
                    ]]
                ])
                writeFile file: '.discord_payload.json', text: payload

                sh '''
                    curl -s -H "Content-Type: application/json" \
                        -X POST \
                        -d @.discord_payload.json \
                        "${DISCORD_DEPLOY_WEBHOOK}?thread_id=1486059245446103051"
                    rm -f .discord_payload.json
                '''
            }
        }
        failure {
            script {
                def sonarLink  = 'https://sonar.igni-soft.com/dashboard?id=Amazingsite-erp'
                def qgStatus   = env.SONAR_QG_STATUS  // 'OK' | 'ERROR' | 'WARN' | 'NONE' | null
                def qgEmoji    = qgStatus == 'OK'    ? '✅' :
                                 qgStatus == 'WARN'  ? '⚠️' :
                                 qgStatus == 'ERROR' ? '❌' :
                                 qgStatus == 'NONE'  ? 'ℹ️' : null
                def sonarValue = qgEmoji
                    ? "${qgEmoji} **${qgStatus}** — [Ver Reporte](${sonarLink})"
                    : "No aplica (rama ${env.BRANCH_NAME})"

                def logSnippet = "Ver log completo: ${env.BUILD_URL}console"
                try {
                    logSnippet = currentBuild.rawBuild.getLog(40).join('\n').replaceAll('`', "'").take(950)
                } catch (ignored) { /* approve via Manage Jenkins > In-process Script Approval */ }

                def payload = groovy.json.JsonOutput.toJson([
                    embeds: [[
                        title: '❌ Error en el Despliegue',
                        description: "El pipeline de **${env.JOB_NAME}** ha fallado.",
                        color: 15158332,
                        fields: [
                            [name: '📝 Commit',              value: "`${env.GIT_COMMIT_HASH ?: 'N/A'}` — ${env.GIT_COMMIT_MSG ?: 'N/A'}", inline: false],
                            [name: '👤 Autor',               value: env.GIT_COMMIT_AUTHOR ?: 'N/A',   inline: true],
                            [name: '🌍 Entorno',             value: env.DEPLOY_ENV ?: 'N/A',          inline: true],
                            [name: '🌿 Rama',                value: "`${env.BRANCH_NAME}`",           inline: true],
                            [name: '🔢 Build',               value: "[#${env.BUILD_NUMBER}](${env.BUILD_URL})", inline: true],
                            [name: '⏱️ Duración',            value: formatDuration(currentBuild.duration), inline: true],
                            [name: '🕐 Fecha',               value: env.BUILD_DATE ?: 'N/A',          inline: true],
                            [name: '📊 SonarQube',           value: sonarValue,                       inline: false],
                            [name: '💥 Stage Fallido',       value: env.FAILED_STAGE ?: 'Desconocido', inline: false],
                            [name: '🔗 Log (últimas líneas)', value: "```\n${logSnippet}\n```",        inline: false]
                        ],
                        footer: [text: 'Jenkins CI/CD']
                    ]]
                ])
                writeFile file: '.discord_payload.json', text: payload

                sh '''
                    curl -s -H "Content-Type: application/json" \
                        -X POST \
                        -d @.discord_payload.json \
                        "${DISCORD_DEPLOY_WEBHOOK}?thread_id=1486059245446103051"
                    rm -f .discord_payload.json
                '''
            }
        }
    }
}