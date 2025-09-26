/**
 * Udfører et asynkront AJAX POST kald til en backend funktion
 * @param {string} url - URL til backend funktion
 * @param {Object} data - Data der skal sendes
 * @returns {Promise} Promise der resolver med svaret eller rejecter med fejlbesked
 */
function performAjaxPost(url, data) {

    return new Promise(function(resolve, reject) {
        // Kontroller at data er angivet
        if (!data) {
            reject('Error: no data');
            return;
        }

        $.ajax({
            url: url, // URL til backend update funktionen
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                // Håndter succesfuldt svar
                if (response && response.status === '1') {

                    let record;
                    try {
                        // Parse JSON strengen hvis nødvendigt
                        record = typeof response.data === 'string' ?
                            JSON.parse(response.data) : response.data;
                    } catch (e) {
                        record = response.data;
                    }
                    resolve(record);
                } else {
                    // Håndter fejl i svaret
                    reject(response.message || 'Error');
                }
            },
            error: function(xhr, status, error) {
                // Håndter AJAX fejl
                reject(error);
            }
        });
    });
}

// Eksempel på brug:
//
// // Update af præsentationsgruppe
// const updateUrl = '/api/presentation/update';
// const groupData = {
//     group_id: '564',
//     title: 'Ny titel',
//     description: 'Opdateret beskrivelse'
// };
//
// performAjaxPost(updateUrl, groupData)
//     .then(function(result) {
//         console.log('Data opdateret:', result);
//     })
//     .catch(function(error) {
//         console.error('Fejl:', error);
//     });
//
// // Oprettelse af bruger
// const createUserUrl = '/api/users/create';
// const userData = {
//     username: 'nybruger',
//     email: 'bruger@example.com',
//     role: 'editor'
// };
//
// performAjaxPost(createUserUrl, userData)
//     .then(function(result) {
//         console.log('Bruger oprettet:', result);
//     })
//     .catch(function(error) {
//         console.error('Fejl:', error);
//     });
//
// // Eksempel på brug med async/await
// async function handleUserRegistration() {
//     try {
//         // Opret bruger
//         const registerUrl = '/api/users/register';
//         const registerData = {
//             username: 'bruger123',
//             email: 'bruger@example.com',
//             password: 'sikkerKode'
//         };
//
//         // Vent på at registreringen gennemføres
//         const registerResult = await performAjaxPost(registerUrl, registerData);
//         console.log('Bruger registreret:', registerResult);
//
//         // Efter vellykket registrering, opret brugerens profil
//         const profileUrl = '/api/profiles/create';
//         const profileData = {
//             user_id: registerResult.id,
//             name: 'John Doe',
//             preferences: { theme: 'dark' }
//         };
//
//         // Vent på at profiloprettelsen gennemføres
//         const profileResult = await performAjaxPost(profileUrl, profileData);
//         console.log('Profil oprettet:', profileResult);
//
//         return { user: registerResult, profile: profileResult };
//     } catch (error) {
//         console.error('Fejl under registreringsprocessen:', error);
//         throw error; // Genkast fejlen til overordnet håndtering
//     }
// }
//
// // Kald den async funktion
// handleUserRegistration()
//     .then(result => {
//         console.log('Hele registreringsprocessen afsluttet:', result);
//     })
//     .catch(error => {
//         console.error('Registrering mislykkedes:', error);
//     });
//     .then(function(result) {
//         console.log('Record opdateret:', result);
//     })
//     .catch(function(error) {
//         console.error('Fejl:', error);
//     });