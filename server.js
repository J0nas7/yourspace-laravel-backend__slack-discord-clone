const express = require('express')
const app = express()
const port = 5000

const axios = require("axios")
axios.defaults.withCredentials = true
const API_URL = "http://127.0.0.1:8000/api/"
let headers = {
    Accept: 'application/json',
    //Authorization: "Bearer "+getCurrentToken(tokenName)
}
let config = {
    withCredentials: true,
    headers: headers,
}

const server = require('http').createServer(app)
const io = require('socket.io')(server, {
    cors: { origin: "*" }
})

server.listen(port, () => {
    console.log("Server is running. Port: " + port)
})

const axiosGet = async (apiEndPoint, config) => {
    const { data: response } = await axios.get(API_URL + apiEndPoint, config)
    return response
}

const axiosPost = async (apiEndPoint, config, postContent) => {
    const { data: response } = await axios.post(API_URL + apiEndPoint, postContent, config)
    return response
}

io.on('connection', (socket) => {
    console.log("Connection")

    socket.on('disconnect', (socket) => {
        console.log("Disconnect")
    })

    socket.on('read', (socket) => {
        console.log("Read")
    })

    socket.on('sendChatToServer', async (data) => {
        let tempConfig = data.config ? data.config : config
        const response = await axiosPost("insertNewMessage", tempConfig, data.post)
        console.log("Response", response)

        if (response.data) {
            const clientData = {
                messageID: 1,
                userID: 1,
                userName: response.data.Profile_DisplayName,
                messageContent: data.post.message,
                messageDate: new Date()
            }
            io.sockets.emit('sendChatToClient', clientData)
            //socket.broadcast.emit('sendChatToClient', message)
        }
    })
})