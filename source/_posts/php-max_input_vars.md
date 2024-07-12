---
extends: _layouts.post
section: content
title: PHP max_input_vars
date: 2024-07-12
language: pt
tag: how-to
---

Eu sempre achava estranho que alguns sites fazem cast do body da requisição para string mas nunca pesquisei o motivo. Recentemente descobri sem querer o por quê.

No zapmizer, a funcionalidade de campanha envia uma requisição com as mensagens e os contatos que vão receber a mensagem. Quando a mensagem tem uma imagem, é necessário que a requisição tenha o formato multipart/form-data. Nesse formato, os parâmetros que são array, são enviados separadamente.

| json                               | multipart/form-data                                                                      |
| ---------------------------------- | ---------------------------------------------------------------------------------------- |
| {<br>"contact_ids": [1,2,3,4]<br>} | `contact_ids[0]: 1`<br>`contact_ids[1]: 2`<br>`contact_ids[2]: 3`<br>`contact_ids[3]: 4` |


Quando o array contact_ids tem muitos elementos, i.e. 1000+, a request vai ser rejeitada. Eu não conhecia que existia um limite para a quantidade de parâmetros numa request, mas faz todo sentido que exista, afinal de contas todo recurso computacional é finito. Por padrão o PHP tem uma configuração `max_input_vars` que limita as variáveis de uma request em **1000**. 

Para contornar essa limitação, temos duas opções:

1. Aumentar o limite da configuração.
2. Alterar o formato input de array para string

**Aumentar o `max_input_vars`**
Essa opção deixa o servidor vulnerável a ataques DDoS, dentre outros riscos. Além do mais, é uma configuração que é aplicada a toda a aplicação, e eu só precisava disso no endpoint de campanhas. 

**Alterar o formato do input**
Essa opção é simples e resolve bem o problema. Basta antes de enviar os parâmetros, transforma-los em string.

### Exemplo Laravel + InertiaJS para vue

```js
const form = useForm('post', route('campaigns.store'), {
    contact_ids: '',
	messages: [], 
});

const selectedContacts = ref({});
watch(selectedContactIds, (value) => {
    form.contact_ids = value.length > 0 ? JSON.stringify(value) : ''; // stringify array
    form.validate('contact_ids');
});

function submit() {
    form.submit({ forceFormData: true });
}
```

```php

class StoreCampaignRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            // contact_ids é recebido como string para evitar que o servidor rejeite a requisição
            // por ter muitos inputs na request - php_ini max_input_vars.
            'contact_ids' => json_decode(request('contact_ids')),
        ]);
    }
}
```


