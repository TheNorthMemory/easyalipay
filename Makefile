all: stats keygen test clean

keygen:
	openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 -out ./tests/fixtures/mock.pkcs8.key
	openssl rsa -in ./tests/fixtures/mock.pkcs8.key -out ./tests/fixtures/mock.pkcs1.key
	openssl rsa -in ./tests/fixtures/mock.pkcs8.key -pubout -out ./tests/fixtures/mock.spki.pub
	openssl rsa -pubin -in ./tests/fixtures/mock.spki.pub -RSAPublicKey_out -out ./tests/fixtures/mock.pkcs1.pub

stats:
	vendor/bin/phpstan analyse --no-progress

test:
	vendor/bin/phpunit

clean:
	rm -rf ./tests/fixtures/mock.*

.PHONY: all
