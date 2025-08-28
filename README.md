# CNPJ Alfanumérico (SEFAZ/Serpro)

Validação e formatação de **CNPJ alfanumérico**: 12 caracteres alfanuméricos + **2 dígitos verificadores numéricos** calculados por **módulo 11** (pesos 2..9 da direita para a esquerda, cíclico). Mapeamento de caracteres: **valor = `ord(ch) - 48`**.

## Instalação

```bash
composer require micdog/cnpj-alfa
```

> Desenvolvimento:
>
> ```bash
> git clone https://github.com/micdog22/CNPJ-Alfanumerico.git
> cd CNPJ-Alfanumerico
> composer install
> ```

## Uso

```php
use MicDog\CnpjAlfa\CnpjAlfa;

// Validar
$ok  = CnpjAlfa::validate('12ABC34501DE35'); // true/false
$fmt = CnpjAlfa::format('12abc34501de35');   // "12.ABC.345/01DE-35"

// Calcular DV (quando você tem só os 12 primeiros)
[$dv1, $dv2] = CnpjAlfa::computeDv('12ABC34501DE'); // [int, int]
```

CLI de demo:

```bash
php bin/demo "12ABC34501DE35" "59.952.259/0001-85"
```

## Regras (resumo)

- Corpo: **12** chars `[A-Z0-9]`
- DV1: módulo 11 aplicando pesos `2..9` **da direita p/ esquerda**.
- DV2: repete com os **13** (12 + DV1).
- Dígitos finais **devem** ser numéricos.

## Testes

```bash
composer test
```

## Licença

MIT
