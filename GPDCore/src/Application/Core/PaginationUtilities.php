<?php

namespace GPDCore\Application\Core;

function encodeCursor($cursor): string
{
    return base64_encode($cursor);
};
function decodeCursor($encodedCursor): string
{
    return base64_decode($encodedCursor);
};
