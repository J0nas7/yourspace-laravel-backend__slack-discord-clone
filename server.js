const express = require('express')
const app = express()

const server = require('http').createServer(app)
const io = require('socket.io')(server, {
    cors: { origin: "*" }
})

const port = 5000

server.listen(port, () => {
    console.log("Server is running. Port: "+port)
})

io.on('connection', (socket) => {
    console.log("Connection")
    
    socket.on('disconnect', (socket) => {
        console.log("Disconnect")
    })
    
    socket.on('read', (socket) => {
        console.log("Read")
    })

    socket.on('sendChatToServer', (message) => {
        io.sockets.emit('sendChatToClient', message)
        //socket.broadcast.emit('sendChatToClient', message)
    })

    /*socket.on('instantMessage', (obj) => {
        console.log(obj)
        socket.broadcast.emit('receiveInstantMessage_'+obj.user, obj.message)
    })*/

})