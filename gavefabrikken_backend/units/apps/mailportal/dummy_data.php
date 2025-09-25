<?php

// Dummy data for mailportal development
class DummyData {
    
    public static function getEmployees() {
        return [
            [
                'id' => 1,
                'name' => 'Lars Nielsen',
                'email' => 'lars.nielsen@company.dk',
                'username' => 'lars.nielsen',
                'password' => 'TempPass123!',
                'mail_sent' => 1,
                'mail_sent_date' => '2024-01-15 10:30:00',
                'last_email_date' => '2024-08-20 14:25:00',
                'email_status' => 'sent',
                'has_error' => 0,
                'error_message' => null,
                'language' => 'da',
                'template_id' => 1,
                'last_email_count' => 2
            ],
            [
                'id' => 2,
                'name' => 'Anna Johansson',
                'email' => 'anna.johansson@company.se',
                'username' => 'anna.johansson',
                'password' => 'SecurePass456!',
                'mail_sent' => 1,
                'mail_sent_date' => '2024-01-15 11:15:00',
                'last_email_date' => '2024-08-19 09:45:00',
                'email_status' => 'sent',
                'has_error' => 0,
                'error_message' => null,
                'language' => 'sv',
                'template_id' => 2,
                'last_email_count' => 1
            ],
            [
                'id' => 3,
                'name' => 'John Smith',
                'email' => 'john.smith@company.com',
                'username' => 'john.smith',
                'password' => 'MyPass789!',
                'mail_sent' => 0,
                'mail_sent_date' => null,
                'last_email_date' => '2024-08-16 16:20:00',
                'email_status' => 'pending',
                'has_error' => 0,
                'error_message' => null,
                'language' => 'en',
                'template_id' => 1,
                'last_email_count' => 0
            ],
            [
                'id' => 4,
                'name' => 'Erik Hansen',
                'email' => 'erik.hansen@company.no',
                'username' => 'erik.hansen',
                'password' => 'NorPass321!',
                'mail_sent' => 1,
                'mail_sent_date' => '2024-01-16 09:45:00',
                'last_email_date' => '2024-08-18 11:30:00',
                'email_status' => 'error',
                'has_error' => 1,
                'error_message' => 'SMTP connection failed',
                'language' => 'no',
                'template_id' => 3,
                'last_email_count' => 3
            ],
            [
                'id' => 5,
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@company.es',
                'username' => 'maria.garcia',
                'password' => 'EsPass654!',
                'mail_sent' => 0,
                'mail_sent_date' => null,
                'last_email_date' => '2024-08-12 13:15:00',
                'email_status' => 'pending',
                'has_error' => 0,
                'error_message' => null,
                'language' => 'en',
                'template_id' => 4,
                'last_email_count' => 0
            ],
            [
                'id' => 6,
                'name' => 'Sven Andersson',
                'email' => 'sven.andersson@company.se',
                'username' => 'sven.andersson',
                'password' => 'SvPass789!',
                'mail_sent' => 1,
                'mail_sent_date' => '2024-01-17 14:20:00',
                'last_email_date' => '2024-08-21 10:05:00',
                'email_status' => 'sent',
                'has_error' => 0,
                'error_message' => null,
                'language' => 'sv',
                'template_id' => 2,
                'last_email_count' => 1
            ]
        ];
    }
    
    public static function getEmailTemplates() {
        return [
            // Standard login templates
            [
                'id' => 1,
                'name' => 'Standard Danish',
                'language' => 'da',
                'subject' => 'Dine login oplysninger',
                'body' => 'Hej {{name}},\n\nHer er dine login oplysninger:\n\nBrugernavn: {{username}}\nAdgangskode: {{password}}\n\nVenlig hilsen\nIT Support',
                'is_default' => 1,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Standard Swedish',
                'language' => 'sv',
                'subject' => 'Dina inloggningsuppgifter',
                'body' => 'Hej {{name}},\n\nHär är dina inloggningsuppgifter:\n\nAnvändarnamn: {{username}}\nLösenord: {{password}}\n\nMed vänliga hälsningar\nIT Support',
                'is_default' => 1,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 3,
                'name' => 'Standard Norwegian',
                'language' => 'no',
                'subject' => 'Dine påloggingsopplysninger',
                'body' => 'Hei {{name}},\n\nHer er dine påloggingsopplysninger:\n\nBrukernavn: {{username}}\nPassord: {{password}}\n\nMed vennlig hilsen\nIT Support',
                'is_default' => 1,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 4,
                'name' => 'Standard English',
                'language' => 'en',
                'subject' => 'Your login credentials',
                'body' => 'Hello {{name}},\n\nHere are your login credentials:\n\nUsername: {{username}}\nPassword: {{password}}\n\nBest regards\nIT Support',
                'is_default' => 1,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-01 00:00:00'
            ],
            
            // Christmas gift templates
            [
                'id' => 5,
                'name' => 'Julegaver Danish',
                'language' => 'da',
                'subject' => 'Julegaver 2024 - Vælg din gave',
                'body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}.header{background:#007bff;color:white;padding:20px;text-align:center;border-radius:5px 5px 0 0}.content{background:#f8f9fa;padding:30px;border:1px solid #dee2e6}.credentials{background:#e7f3ff;border:1px solid #b8daff;border-radius:5px;padding:15px;margin:20px 0}.footer{background:#6c757d;color:white;padding:15px;text-align:center;border-radius:0 0 5px 5px}</style></head><body><div class="header"><h1>Julegaver 2024</h1></div><div class="content"><h2>Kære {{name}},</h2><p>Juletiden nærmer sig – og igen i år har du mulighed for at vælge mellem en række forskellige lækre gaver.</p><p>Valg af gave foregår via nedenstående link og login.</p><div class="credentials"><h3>Vælg din gave her:</h3><p><a href="{{link}}" style="color:#007bff;">{{link}}</a></p><p><strong>Brugernavn:</strong> {{username}}<br><strong>Adgangskode:</strong> {{password}}</p></div><p>Når du er logget ind på shoppen, kan du vælge din gave ud fra de viste billeder og beskrivelser.</p><p>Shoppen er åben fra <strong>{{start_date}}</strong> til <strong>{{end_date}}</strong>. I denne periode kan du frit vælge og ændre dit valg til en anden gave.</p><p><em>Har du ikke valgt en gave i shoppens åbningsperiode, vil du automatisk modtage en gavekurv fyldt med lækkerier.</em></p><p>Vi tager forbehold for udsolgte varer.</p><p>Rigtig god fornøjelse.</p></div><div class="footer"><p>Med venlig hilsen<br><strong>GaveFabrikken</strong></p></div></body></html>',
                'is_default' => 0,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-15 12:00:00'
            ],
            [
                'id' => 6,
                'name' => 'Christmas Gifts English',
                'language' => 'en',
                'subject' => 'Christmas Gifts 2024 - Choose your gift',
                'body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}.header{background:#007bff;color:white;padding:20px;text-align:center;border-radius:5px 5px 0 0}.content{background:#f8f9fa;padding:30px;border:1px solid #dee2e6}.credentials{background:#e7f3ff;border:1px solid #b8daff;border-radius:5px;padding:15px;margin:20px 0}.footer{background:#6c757d;color:white;padding:15px;text-align:center;border-radius:0 0 5px 5px}</style></head><body><div class="header"><h1>Christmas Gifts 2024</h1></div><div class="content"><h2>Dear {{name}},</h2><p>Christmas time is approaching – and once again this year you have the opportunity to choose from a range of different delicious gifts.</p><p>Gift selection is done via the link and login below.</p><div class="credentials"><h3>Choose your gift here:</h3><p><a href="{{link}}" style="color:#007bff;">{{link}}</a></p><p><strong>Username:</strong> {{username}}<br><strong>Password:</strong> {{password}}</p></div><p>Once logged into the shop, you can choose your gift based on the displayed images and descriptions.</p><p>The shop is open from <strong>{{start_date}}</strong> until <strong>{{end_date}}</strong>. During this period you can freely choose and change your selection to another gift.</p><p><em>If you have not chosen a gift during the shop\'s opening period, you will automatically receive a gift basket filled with delicacies.</em></p><p>We reserve the right for sold-out items.</p><p>Enjoy!</p></div><div class="footer"><p>Best regards<br><strong>GaveFabrikken</strong></p></div></body></html>',
                'is_default' => 0,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-15 12:00:00'
            ],
            [
                'id' => 7,
                'name' => 'Julklappar Swedish',
                'language' => 'sv',
                'subject' => 'Julklappar 2024 - Välj din present',
                'body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}.header{background:#007bff;color:white;padding:20px;text-align:center;border-radius:5px 5px 0 0}.content{background:#f8f9fa;padding:30px;border:1px solid #dee2e6}.credentials{background:#e7f3ff;border:1px solid #b8daff;border-radius:5px;padding:15px;margin:20px 0}.footer{background:#6c757d;color:white;padding:15px;text-align:center;border-radius:0 0 5px 5px}</style></head><body><div class="header"><h1>Julklappar 2024</h1></div><div class="content"><h2>Kära {{name}},</h2><p>Jultiden närmar sig – och även i år har du möjlighet att välja mellan en rad olika läckra presenter.</p><p>Val av present sker via länken och inloggning nedan.</p><div class="credentials"><h3>Välj din present här:</h3><p><a href="{{link}}" style="color:#007bff;">{{link}}</a></p><p><strong>Användarnamn:</strong> {{username}}<br><strong>Lösenord:</strong> {{password}}</p></div><p>När du är inloggad i butiken kan du välja din present utifrån de visade bilderna och beskrivningarna.</p><p>Butiken är öppen från <strong>{{start_date}}</strong> fram till <strong>{{end_date}}</strong>. Under denna period kan du fritt välja och ändra ditt val till en annan present.</p><p><em>Om du inte har valt en present under butikens öppetperiod kommer du automatiskt att få en presentkorg fylld med delikatesser.</em></p><p>Vi tar förbehåll för slutsålda varor.</p><p>Riktigt bra nöje.</p></div><div class="footer"><p>Med vänliga hälsningar<br><strong>GaveFabrikken</strong></p></div></body></html>',
                'is_default' => 0,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-15 12:00:00'
            ],
            [
                'id' => 8,
                'name' => 'Julegaver Norwegian',
                'language' => 'no',
                'subject' => 'Julegaver 2024 - Velg din gave',
                'body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px}.header{background:#007bff;color:white;padding:20px;text-align:center;border-radius:5px 5px 0 0}.content{background:#f8f9fa;padding:30px;border:1px solid #dee2e6}.credentials{background:#e7f3ff;border:1px solid #b8daff;border-radius:5px;padding:15px;margin:20px 0}.footer{background:#6c757d;color:white;padding:15px;text-align:center;border-radius:0 0 5px 5px}</style></head><body><div class="header"><h1>Julegaver 2024</h1></div><div class="content"><h2>Kjære {{name}},</h2><p>Juletiden nærmer seg – og igjen i år har du mulighet til å velge mellom en rekke forskjellige deilige gaver.</p><p>Valg av gave skjer via lenken og innlogging nedenfor.</p><div class="credentials"><h3>Velg din gave her:</h3><p><a href="{{link}}" style="color:#007bff;">{{link}}</a></p><p><strong>Brukernavn:</strong> {{username}}<br><strong>Passord:</strong> {{password}}</p></div><p>Når du er logget inn i butikken, kan du velge din gave ut fra de viste bildene og beskrivelsene.</p><p>Butikken er åpen fra <strong>{{start_date}}</strong> frem til <strong>{{end_date}}</strong>. I denne perioden kan du fritt velge og endre ditt valg til en annen gave.</p><p><em>Har du ikke valgt en gave i butikkens åpningsperiode, vil du automatisk motta en gavekurv fylt med delikatesser.</em></p><p>Vi tar forbehold om utsolgte varer.</p><p>Virkelig god fornøyelse.</p></div><div class="footer"><p>Med vennlig hilsen<br><strong>GaveFabrikken</strong></p></div></body></html>',
                'is_default' => 0,
                'shop_type' => 'valgshop',
                'created_date' => '2024-01-15 12:00:00'
            ],
            
            // Formal template
            [
                'id' => 9,
                'name' => 'Formal Danish',
                'language' => 'da',
                'subject' => 'Adgang til virksomhedens systemer',
                'body' => 'Kære {{name}},\n\nDu har nu fået adgang til virksomhedens IT-systemer.\n\nDine login oplysninger er:\nBrugernavn: {{username}}\nAdgangskode: {{password}}\n\nBemærk at du skal skifte adgangskode ved første login.\n\nHvis du har spørgsmål, er du velkommen til at kontakte IT-afdelingen.\n\nVenlig hilsen\nIT-afdelingen',
                'is_default' => 0,
                'shop_type' => 'kortshop',
                'created_date' => '2024-01-10 12:00:00'
            ]
        ];
    }
    
    public static function getLanguages() {
        return [
            ['code' => 'da', 'name' => 'Dansk'],
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'sv', 'name' => 'Svenska'],
            ['code' => 'no', 'name' => 'Norsk'],
            ['code' => 'de', 'name' => 'Deutsch']
        ];
    }
    
    public static function getEmailHistory($employee_id = null) {
        $history = [
            [
                'id' => 1,
                'employee_id' => 1,
                'template_id' => 1,
                'subject' => 'Dine login oplysninger',
                'sent_date' => '2024-01-15 10:30:00',
                'status' => 'sent',
                'error_message' => null,
                'attempt_number' => 1
            ],
            [
                'id' => 2,
                'employee_id' => 1,
                'template_id' => 1,
                'subject' => 'Dine login oplysninger - Påmindelse',
                'sent_date' => '2024-01-18 09:15:00',
                'status' => 'sent',
                'error_message' => null,
                'attempt_number' => 2
            ],
            [
                'id' => 3,
                'employee_id' => 2,
                'template_id' => 2,
                'subject' => 'Dina inloggningsuppgifter',
                'sent_date' => '2024-01-15 11:15:00',
                'status' => 'sent',
                'error_message' => null,
                'attempt_number' => 1
            ],
            [
                'id' => 4,
                'employee_id' => 4,
                'template_id' => 3,
                'subject' => 'Dine påloggingsopplysninger',
                'sent_date' => '2024-01-16 09:45:00',
                'status' => 'failed',
                'error_message' => 'SMTP connection failed',
                'attempt_number' => 1
            ],
            [
                'id' => 5,
                'employee_id' => 4,
                'template_id' => 3,
                'subject' => 'Dine påloggingsopplysninger',
                'sent_date' => '2024-01-16 10:30:00',
                'status' => 'failed',
                'error_message' => 'SMTP timeout',
                'attempt_number' => 2
            ],
            [
                'id' => 6,
                'employee_id' => 4,
                'template_id' => 3,
                'subject' => 'Dine påloggingsopplysninger',
                'sent_date' => '2024-01-16 15:20:00',
                'status' => 'sent',
                'error_message' => null,
                'attempt_number' => 3
            ],
            [
                'id' => 7,
                'employee_id' => 6,
                'template_id' => 2,
                'subject' => 'Dina inloggningsuppgifter',
                'sent_date' => '2024-01-17 14:20:00',
                'status' => 'sent',
                'error_message' => null,
                'attempt_number' => 1
            ]
        ];
        
        if ($employee_id) {
            return array_filter($history, function($item) use ($employee_id) {
                return $item['employee_id'] == $employee_id;
            });
        }
        
        return $history;
    }
    
    public static function getShopInfo() {
        // I praksis ville dette komme fra database eller konfiguration
        // Simuler forskellige kunder - i praksis ville dette være baseret på login/session
        $customers = [
            [
                'type' => 'valgshop',
                'name' => 'Valgshop',
                'customer_name' => 'Test Firma ApS',
                'customer_short' => 'Test Firma',
                'description' => 'Shop hvor medarbejdere kan vælge mellem forskellige gaver',
                'url' => 'https://shop.gavefabrikken.dk/valgshop/',
            ],
            [
                'type' => 'kortshop',
                'name' => 'Kortshop',
                'customer_name' => 'Demo Corporation A/S',
                'customer_short' => 'Demo Corp',
                'description' => 'Kortbaseret gaveshop til medarbejdere',
                'url' => 'https://shop.gavefabrikken.dk/kortshop/',
            ],
            [
                'type' => 'valgshop',
                'name' => 'Valgshop',
                'customer_name' => 'Eksempel Virksomhed I/S',
                'customer_short' => 'Eksempel Virk.',
                'description' => 'Premium gaveshop med valgmuligheder',
                'url' => 'https://shop.gavefabrikken.dk/premium/',
            ]
        ];
        
        // For demonstration - returnér første kunde
        // I praksis ville dette være baseret på bruger session/login
        $selectedCustomer = $customers[0];
        
        $selectedCustomer['features'] = [
            'gift_selection' => $selectedCustomer['type'] === 'valgshop',
            'multiple_languages' => true,
            'deadline_based' => true
        ];
        
        return $selectedCustomer;
    }
    
    public static function getMailServers() {
        return [
            [
                'id' => 1,
                'name' => 'Primary SMTP Server',
                'host' => 'smtp.gavefabrikken.dk',
                'port' => 587,
                'username' => 'noreply@gavefabrikken.dk',
                'encryption' => 'tls',
                'is_default' => true,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Backup SMTP Server',
                'host' => 'backup-smtp.gavefabrikken.dk',
                'port' => 465,
                'username' => 'backup@gavefabrikken.dk',
                'encryption' => 'ssl',
                'is_default' => false,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'SendGrid Service',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'username' => 'apikey',
                'encryption' => 'tls',
                'is_default' => false,
                'status' => 'active'
            ]
        ];
    }
    
    public static function getSendings() {
        return [
            [
                'id' => 1,
                'template_name' => 'Login Credentials',
                'created_date' => '2024-08-20 10:30:00',
                'status' => 'completed',
                'total_recipients' => 4,
                'sent_count' => 3,
                'error_count' => 1,
                'recipients' => [1, 2, 3, 4],
                'progress' => 100,
                'recipient_status' => [
                    1 => ['status' => 'sent', 'sent_date' => '2024-08-20 10:32:00'],
                    2 => ['status' => 'sent', 'sent_date' => '2024-08-20 10:33:00'],
                    3 => ['status' => 'pending', 'sent_date' => null],
                    4 => ['status' => 'error', 'sent_date' => '2024-08-20 10:34:00', 'error_message' => 'SMTP timeout']
                ]
            ],
            [
                'id' => 2,
                'template_name' => 'Christmas Gift Info',
                'created_date' => '2024-08-19 14:15:00',
                'status' => 'completed',
                'total_recipients' => 6,
                'sent_count' => 5,
                'error_count' => 1,
                'recipients' => [1, 2, 3, 4, 5, 6],
                'progress' => 100,
                'recipient_status' => [
                    1 => ['status' => 'sent', 'sent_date' => '2024-08-19 14:17:00'],
                    2 => ['status' => 'sent', 'sent_date' => '2024-08-19 14:18:00'],
                    3 => ['status' => 'pending', 'sent_date' => null],
                    4 => ['status' => 'error', 'sent_date' => '2024-08-19 14:19:00', 'error_message' => 'Invalid email format'],
                    5 => ['status' => 'sent', 'sent_date' => '2024-08-19 14:20:00'],
                    6 => ['status' => 'sent', 'sent_date' => '2024-08-19 14:21:00']
                ]
            ],
            [
                'id' => 3,
                'template_name' => 'Password Reset',
                'created_date' => '2024-08-18 09:00:00',
                'status' => 'in_progress',
                'total_recipients' => 3,
                'sent_count' => 2,
                'error_count' => 0,
                'recipients' => [2, 5, 6],
                'progress' => 67,
                'recipient_status' => [
                    2 => ['status' => 'sent', 'sent_date' => '2024-08-18 09:02:00'],
                    5 => ['status' => 'pending', 'sent_date' => null],
                    6 => ['status' => 'sent', 'sent_date' => '2024-08-18 09:03:00']
                ]
            ],
            [
                'id' => 4,
                'template_name' => 'Julegaver Danish',
                'created_date' => '2024-08-21 11:45:00',
                'status' => 'completed',
                'total_recipients' => 5,
                'sent_count' => 4,
                'error_count' => 1,
                'recipients' => [1, 3, 4, 5, 6],
                'progress' => 100,
                'recipient_status' => [
                    1 => ['status' => 'sent', 'sent_date' => '2024-08-21 11:47:00'],
                    3 => ['status' => 'pending', 'sent_date' => null],
                    4 => ['status' => 'error', 'sent_date' => '2024-08-21 11:48:00', 'error_message' => 'Connection refused'],
                    5 => ['status' => 'sent', 'sent_date' => '2024-08-21 11:49:00'],
                    6 => ['status' => 'sent', 'sent_date' => '2024-08-21 11:50:00']
                ]
            ]
        ];
    }
}